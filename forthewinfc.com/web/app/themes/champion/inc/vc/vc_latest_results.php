<?php

if (!defined('ABSPATH')) die('-1');

class VCLatestResultsClass {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrateWithVC' ) );
 
        // Use this when creating a shortcode addon
        add_shortcode( 'stm_lr', array( $this, 'render' ) );

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
                "name" => __("Latest Results", 'champion'),
                "description" => __("Place Latest Results", 'champion'),
                "base" => "stm_lr",
                "class" => "latest_match_results",
                "controls" => "full",
                "icon" => 'vc_latest_results_icon',
                "category" => __('STM', 'champion'),
                'admin_enqueue_css' => array(get_template_directory_uri() . '/inc/vc/assets/css/vc_latest_results_admin.css'), // This will load css file in the VC backend editor
                "params" => array(

                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => __("Title", 'champion'),
                        "param_name" => "title",
                        "value" => __("Latest Results", 'champion'),
                        "description" => __("Enter text which will be used as widget title. Leave blank if no title is needed.", 'js_composer')
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
            'title' => 'Latest Results',
            'pick_team' => ''
        ), $atts ) );

        $output = '';
		$id = false;
        $latest_results = get_posts(array('post_status' => 'publish', 'posts_per_page' => 9999, 'post_type' => 'sp_event'));
        $latest_post = array();
        if($latest_results){
            foreach($latest_results as $post){
	            if( ! empty( $pick_team ) ){
		            if( in_array( $pick_team, get_post_meta($post->ID, 'sp_team' ) ) ){
			            $id = $post->ID;
		                $latest_post = $post;
		                break;
		            }
	            }else{
		            $id = $post->ID;
	                $latest_post = $post;
	                break;
	            }
            }
        }else{
	        return;
        }
		if( $id ){
			$event = new SP_Event( $id );
	        $data = $event->results();
	        $performance = $event->performance();
	        $date = get_the_time( get_option('date_format'), $id );
	        $time = get_the_time( get_option('time_format'), $id );
	        $venues = get_the_terms( $id, 'sp_venue' );
	        unset( $data[0] );
	        unset( $performance[0] );
	
	        if($performance){
	            foreach($performance as $key => $val){
	                unset( $performance[$key][0] );
	            }
	        }
	        if(!empty($data)){
				
				$sportspress_primary_result = get_option( 'sportspress_primary_result', null );
				if( !empty( $sportspress_primary_result ) )
					$goals = $sportspress_primary_result;
				else
					$goals = "goals" ;
	
	            $output .= '<div class="vc_latest_result"><div class="fixture_detail clearfix">';
	                $output .= '<h2>'.esc_html( $title) .'</h2>';
	                $output .= '<h3>'.esc_html( $latest_post->post_excerpt ).'</h3>';
	                $i=0; foreach($data as $team_id => $result){ $i++;
	
	                    $wr_class = ($i == 2) ? 'command_right' : 'command_left';
	
	                    $output .= '<div class="'.esc_attr( $wr_class ).'">';
	                    $output .= '<div class="command_info">';
	                    $output .= '<div class="logo"><a href="'.esc_url( get_the_permalink($team_id) ).'">'.get_the_post_thumbnail($team_id, "team_logo").'</a></div>';
		                $output .= '<div class="score">';
	                    if( isset( $result[$goals] ) ){
		                    $output .= esc_html( $result[$goals] );
	                    }
		                $output .= '</div>';
	                    $output .= '</div>';
	                    $output .= '<div class="goals">';
	                    $output .= '<h2><a href="'.esc_url( get_the_permalink($team_id) ).'">'.esc_html( get_the_title( $team_id ) ).'</a></h2>';
	                    if( isset( $result['outcome'][0] ) ){
		                    $outcome = get_page_by_path( $result['outcome'][0], OBJECT, 'sp_outcome' );
		                    $output .= '<h4>'. get_the_title( $outcome->ID ) .'</h4>';
	                    }
	                    if($performance){
	                        $output .= '<ul class="players">';
	                        foreach($performance[$team_id] as $player_id => $player){
		                        if( isset( $player[$goals] ) ){
			                        if($player[$goals] >= 1){
		                                $output .= '<li>'.esc_html( get_the_title($player_id) ).' - <span>'.esc_html( $player[$goals] ).' '.__('goal(s)', 'champion').'</span></li>';
		                            }
		                        }
	                        }
	                        $output .= '</ul>';
	                    }
	                    $output .= '</div>';
	                    $output .= '</div>';
	                }
	                $output .= '<div class="fixture_info">';
	                    $output .= '<div class="date_time">'.esc_html( $date.'  | '.$time ).'</div>';
						if( !empty( $venues ) ){
							foreach( $venues as $venue ){
								$output .= '<div class="venue">'.esc_html( $venue->name ).'</div>';
							}
						}
	                    
	                    $output .= '<a class="btn btn-danger btn-lg" href="'.esc_url( get_the_permalink($id) ).'"><span>'.__('read more', 'champion').'</span></a>';
	                $output .= '</div>';
	            $output .= '</div></div>';
	        }
		}

        return $output;
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

if (!function_exists('is_plugin_active')) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if ( is_plugin_active( 'sportspress/sportspress.php' ) || is_plugin_active( 'sportspress-pro/sportspress-pro.php' ) && defined( 'WPB_VC_VERSION' ) ) {

    new VCLatestResultsClass();

}