<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class VCPlayersCarouselClass {
	function __construct() {
		// We safely integrate with VC with this hook
		add_action( 'init', array( $this, 'integrateWithVC' ) );

		// Use this when creating a shortcode addon
		add_shortcode( 'stm_players_carousel', array( $this, 'render' ) );

		// Register CSS and JS
		add_action( 'wp_enqueue_scripts', array( $this, 'loadCssAndJs' ) );
	}

	public function integrateWithVC() {
		// Check if Visual Composer is installed
		if ( ! defined( 'WPB_VC_VERSION' ) ) {
			// Display notice that Visual Compser is required
			add_action( 'admin_notices', array( $this, 'showVcVersionNotice' ) );

			return;
		}

		$player_lists = get_posts(array('post_type' => 'sp_list', 'posts_per_page' => 9999));
		$teams = get_posts(array('post_type' => 'sp_team', 'posts_per_page' => 9999));

		$lists_array = array();
		$teams_array = array();

		if($player_lists){
			foreach($player_lists as $list){
				$lists_array[$list->post_title] = $list->ID;
			}
		}

		if($teams){
			foreach($teams as $team){
				$teams_array[$team->post_title] = $team->ID;
			}
		}

		if ( function_exists( "vc_map" ) ) {
			vc_map( array(
				"name"              => __( "Players Carousel", 'champion' ),
				"description"       => __( "Place Players Carousel", 'champion' ),
				"base"              => "stm_players_carousel",
				"class"             => "latest_players_carousel",
				"controls"          => "full",
				"icon"              => 'vc_players_carousel_icon',
				"category"          => __( 'STM', 'champion' ),
				//'admin_enqueue_css' => array( get_template_directory_uri() . '/inc/vc/assets/css/vc_products_carousel_admin.css' ),
				// This will load css file in the VC backend editor
				"params"            => array(

					array(
						"type"        => "dropdown",
						"heading"     => __( "Team", 'champion' ),
						"param_name"  => "team",
						"width"       => 150,
						"value"       => $teams_array,
						"admin_label" => true
					),
					array(
						"type"        => "dropdown",
						"heading"     => __( "Player Lists", 'champion' ),
						"param_name"  => "player_list",
						"width"       => 150,
						"value"       => $lists_array,
						"admin_label" => true
					)
				)
			) );
		}
	}

	/*
	Shortcode logic how it should be rendered
	*/
	public function render( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'player_list'      => '',
			'team'         =>  ''
		), $atts ) );


		$output = '';

		$rand = mt_rand( 555, 9999 );

		$id = $team;

		$team = new SP_Team( $id );
		$lists = $team->lists();

		$output .= '<ul id="owl-' . esc_attr( $rand ) . '" class="player_gallery players_carousel">';
			foreach ( $lists as $list ) {

				if($list->ID == $player_list){

					$list = new SP_Player_List( $list->ID );
					$data = $list->data();
					unset( $data[0] );

					foreach ($data as $player_id => $performance) {

						$positions = wp_get_post_terms($player_id,'sp_position');
						$position = false;
						if($positions){
							$position = $positions[0]->name;
						}

						$id = $player_id;

						if ( has_post_thumbnail( $id ) ) {
							$thumbnail = get_the_post_thumbnail( $id, 'player_photo' );
						}else {
							$thumbnail = '<img width="740" height="740" src="http://www.gravatar.com/avatar/?s=740&d=mm&f=y" class="attachment-thumbnail wp-post-image">';
						}

						$player_number = get_post_meta( $id, 'sp_number', true );

						$output .= '<li>';
						$output .= '<div class="player_image">';
						$output .= '<a href="'. esc_url( get_the_permalink($id) ) .'">';
						$output .= $thumbnail;
						$output .= '</a>';
						$output .= '<div class="player_like">';
						$like = ( get_post_meta($id, 'stm_like', true) == '' ) ? 0 : get_post_meta($id, 'stm_like', true);
						$output .= '<a href="#" class="like_button" data-id="'.esc_attr( $id ).'" onclick="stm_like(jQuery(this)); return false;"><i class="fa fa-heart"></i> <span>'.$like.'</span></a>';
						$output .= '</div>';
						$output .= '</div>';
						$output .= '<h4><a href="'.esc_url( get_the_permalink($id)).'">'.get_the_title($id).'</a></h4>';
						$output .= '<div class="player_info clearfix">';
						$output .= '<div class="number"><i class="t-shirt"></i> '.$player_number.'</div>';
						$output .= '<div class="position">'.$position.'</div>';
						$output .= '</div>';
						$output .= '</li>';
					}


				}
			}
		$output .= '</ul>';

		$output .= '
                <script>
                    jQuery(document).ready(function($) {
                        var owl = jQuery("#owl-' . $rand . '");

                        owl.owlCarousel({
                            navigation : true,
                            itemsTablet: [600,3],
                            pagination : false,
                            navigationText : ["<i class=\"fa fa-angle-left\"></i>","<i class=\"fa fa-angle-right\"></i>"]
                        });
                    });
                </script>
            ';

		return $output;
	}

	/*
	Load plugin css and javascript files which you may need on front end of your site
	*/
	public function loadCssAndJs() {
		//wp_enqueue_style( 'owl_carousel', get_template_directory_uri() . '/assets/css/owl.carousel.css', null, ( WP_DEBUG ) ? time() : null );
		wp_enqueue_script( 'owl_carousel', get_template_directory_uri() . '/assets/js/owl.carousel.min.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );
	}

	/*
	Show notice if your plugin is activated but Visual Composer is not
	*/
	public function showVcVersionNotice() {
		$plugin_data = get_plugin_data( __FILE__ );
		echo '
        <div class="updated">
        <p>' . sprintf( __( '<strong>%s</strong> requires <strong><a href="#" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend' ), $plugin_data['Name'] ) . '</p>
        </div>';
	}
}

if ( is_plugin_active( 'sportspress/sportspress.php' ) || is_plugin_active( 'sportspress-pro/sportspress-pro.php' ) && defined( 'WPB_VC_VERSION' ) ) {

	new VCPlayersCarouselClass();

}