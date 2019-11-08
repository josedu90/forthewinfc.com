<?php

if (!defined('ABSPATH')) die('-1');

if( !class_exists('VCNextMatchClass') ){
	
	class VCNextMatchClass {
		function __construct() {
			// We safely integrate with VC with this hook
			add_action( 'init', array( $this, 'integrateWithVC' ) );
	 
			// Use this when creating a shortcode addon
			add_shortcode( 'stm_nm', array( $this, 'render' ) );

			// Register CSS and JS
			//add_action( 'wp_enqueue_scripts', array( $this, 'loadCssAndJs' ) );
		}
	 
		public function integrateWithVC() {
			// Check if Visual Composer is installed
			if ( ! defined( 'WPB_VC_VERSION' ) ) {
				// Display notice that Visual Compser is required
				add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
				return;
			}
			if(function_exists("vc_map")) {
				
				$teams = get_posts(array('post_type' => 'sp_team', 'posts_per_page' => 9999));
				$teams_array = array( __( 'All', 'champion' ) => 0 );
				if($teams){
					foreach($teams as $team){
						$teams_array[$team->post_title] = $team->ID;
					}
				}
				
				vc_map(array(
					"name" => __("Next Match", 'champion'),
					"description" => __("Place Next Match", 'champion'),
					"base" => "stm_nm",
					"class" => "next_match",
					"controls" => "full",
					"icon" => 'vc_next_match_icon',
					"category" => __('STM', 'champion'),
					'admin_enqueue_css' => array(get_template_directory_uri() . '/inc/vc/assets/css/vc_next_match_admin.css'), // This will load css file in the VC backend editor
					"params" => array(

						array(
							"type" => "textfield",
							"holder" => "div",
							"class" => "",
							"heading" => __("Title", 'champion'),
							"param_name" => "title",
							"value" => __("Next Match", 'champion'),
							"description" => __("Enter text which will be used as widget title. Leave blank if no title is needed.", 'js_composer')
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Show", 'champion'),
							"description" => __("Fixtures by only this team will be displayed", 'champion'),
							"param_name" => "show_games",
							"value" => array(
								__( 'Certain number', 'champion' ) => 'number',
								__( 'All games', 'champion' ) => 'all'
							)
						),
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Count", 'champion'),
							"param_name" => "count",
							"value" => 1,
							"min" => 1,
							"dependency" => array(
								"element" => "show_games",
								"value" => array( "number" ),
							),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Pick a team", 'champion'),
							"description" => __("Fixtures by only this team will be displayed", 'champion'),
							"param_name" => "pick_team",
							"value" => $teams_array
						),
					)
				));
			}
		}
		
		/*
		Shortcode logic how it should be rendered
		*/
		public function render( $atts, $content = null ) {

			extract( shortcode_atts( array(
				'title' => 'Next Match',
				'count' => 1,
				'pick_team' => '',
				'show_games' => 'number'
			), $atts ) );
			
			if( $show_games == 'all' ){
				$count = 9999;
			}

			$next_matches_array = get_posts(array('post_status' => 'future', 'posts_per_page' => 9999, 'post_type' => 'sp_event', 'order' => 'ASC'));
			$next_matches = array();
			$i = 0;
			if($next_matches_array){
				foreach($next_matches_array as $post){
					if( ! empty( $pick_team ) ){
						if( in_array( $pick_team, get_post_meta($post->ID, 'sp_team' ) ) ){
							$i++;
							$next_matches[] = $post;
							if( $i == $count ){
								break;
							}
						}
					}else{
						$i++;
						$next_matches[] = $post;
						if( $i == $count ){
							break;
						}
					}
				}
			}
			$output = '';
			if($next_matches){
				
				$output .= '<div class="vc_next_match">';
				if( $title ){
					$output .= '<div class="title"><h4>'.$title.'</h4></div>';
				}
				
				foreach($next_matches as $post){
					$id = $post->ID;
					$event = new SP_Event( $id );
					$data = $event->results();
					unset( $data[0] );
					$venues = get_the_terms( $id, 'sp_venue' );
		
					$now = new DateTime( current_time( 'mysql', 0 ) );
					$date = new DateTime( $post->post_date );
					$interval = date_diff( $now, $date );
		
					$days = $interval->invert ? 0 : $interval->days;
					$h = $interval->invert ? 0 : $interval->h;
					$i = $interval->invert ? 0 : $interval->i;
					$s = $interval->invert ? 0 : $interval->s;

					if ( $days >= 10 ) {
						$output .= '<p class="countdown sp-countdown long-countdown clear">';
					}else {
						$output .= '<p class="countdown sp-countdown clear">';
					}

					$output .= '<time datetime="'.esc_attr( $post->post_date ) .'"  data-countdown="'.esc_attr( str_replace( "-", "/", $post->post_date ) ).'">';
						$output .= '<span>'.sprintf( '%02s', $days ).' <small>'.__( 'days', 'sportspress' ).'</small></span>';
						$output .= '<span>'.sprintf( '%02s', $h ).' <small>'.__( 'hrs', 'sportspress' ).'</small></span>';
						$output .= '<span>'.sprintf( '%02s', $i ).' <small>'.__( 'mins', 'sportspress' ).'</small></span>';
						$output .= '<span>'.sprintf( '%02s', $s ).' <small>'.__( 'secs', 'sportspress' ).'</small></span>';
					$output .= '</time>';
					$output .= '</p>';

					$output .= '<div class="commands">';

					$i=0; foreach($data as $team_id => $result) { $i++;

						$output .= '<div class="command">';
							$output .= '<div class="logo"><a href="'.esc_url( get_the_permalink($team_id) ) .'">'.get_the_post_thumbnail($team_id, 'team_logo').'</a></div>';
							$output .= '<h5><a href="'. esc_url( get_the_permalink($team_id) ) .'">'.get_the_title( $team_id ).'</a></h5>';
						$output .= '</div>';

						if($i == 1){
							$output .= '<div class="command_vs"><span>-</span> '.__('VS', 'champion').' <span>-</span></div>';
						}

					}
					$output .= '</div>';

					$output .= '<div class="match_info">';
					$output .= get_the_time( get_option('date_format'), $id ).'<br/>';
					if($venues){
						foreach( $venues as $venue ){
							$output .= $venue->name;
						}
					}
					$output .= '</div>';
				}
				$output .= '</div>';
				
			}

			return $output;
		}

		/*
		Load plugin css and javascript files which you may need on front end of your site
		*/
		public function loadCssAndJs() {
			wp_enqueue_style( 'vc_next_match', get_template_directory_uri().'/inc/vc/assets/css/vc_next_match.css', null, ( WP_DEBUG ) ? time() : null );
		}

		/*
		Show notice if your plugin is activated but Visual Composer is not
		*/
		public function showVcVersionNotice() {
			$plugin_data = get_plugin_data(__FILE__);
			echo '
			<div class="updated">
			<p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="#" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend'), $plugin_data['Name']).'</p>
			</div>';
		}
	}

	if ( is_plugin_active( 'sportspress/sportspress.php' ) || is_plugin_active( 'sportspress-pro/sportspress-pro.php' ) && defined( 'WPB_VC_VERSION' ) ) {

		new VCNextMatchClass();

	}
	
}