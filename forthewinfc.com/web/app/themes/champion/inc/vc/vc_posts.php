<?php

if (!defined('ABSPATH')) die('-1');

class VCPostsClass {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrateWithVC' ) );
 
        // Use this when creating a shortcode addon
        add_shortcode( 'stm_posts', array( $this, 'render' ) );

    }
 
    public function integrateWithVC() {
        // Check if Visual Composer is installed
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            // Display notice that Visual Compser is required
            add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
            return;
        }
        if(function_exists("vc_map")) {
            vc_map(array(
                "name" => __("Posts", 'champion'),
                "description" => __("Create WordPress loop", 'champion'),
                "base" => "stm_posts",
                "class" => "stm_posts",
                "controls" => "full",
                "icon" => 'vc_posts_icon',
                "category" => __('STM', 'champion'),
                //'admin_enqueue_css' => array(get_template_directory_uri() . '/inc/vc/assets/css/vc_next_match_admin.css'), // This will load css file in the VC backend editor
                "params" => array(

	                array(
		                "type" => "loop",
		                "heading" => __("Content", 'champion'),
		                "param_name" => "loop",
		                'settings' => array(
			                'size' => array('hidden' => false, 'value' => 4),
			                'order_by' => array('value' => 'date'),
			                'post_type' => array('value' => 'post')
		                ),
		                "description" => __("Create WordPress loop.", 'champion')
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
            'loop' => ''
        ), $atts ) );

	    if(empty($loop)) return;

	    $query = false;

	    list($loop_args, $query)  = vc_build_loop_query($loop, get_the_ID());

	    if(!$query) return;

	    $output = '<div class="posts_list">';

		if($query->have_posts()){

			while($query->have_posts()){ $query->the_post();
				$output .= '<article class="'.esc_attr( implode(' ', get_post_class()) ).'">';
				$output .= '<div class="post-thumbnail">';
				$output .= '<a href="'.esc_url( get_the_permalink() ).'">';

				if(has_post_thumbnail()){
					$output .= get_the_post_thumbnail(get_the_ID(), 'blog_list');
				}else{
					if(is_sticky()){
						$output .= '<img src="'.get_template_directory_uri().'/assets/images/no-image-red.png" alt="'.esc_attr( get_the_title() ).'">';
					}else{
						$output .= '<img src="'.get_template_directory_uri().'/assets/images/no-image.png" alt="'.esc_attr( get_the_title() ).'">';
					}
				}
				$output .= '</a>';
				$output .= '</div>';
				$output .= '<h2 class="entry-title"><a href="' . esc_url( get_the_permalink() ) . '">'.get_the_title().'</a></h2>';
				$output .= '<p>'.get_the_excerpt().'</p>';
				$output .= '<div class="post_info clearfix">';
				$output .= '<div class="date">'.get_the_date().'</div>';
				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ){
					$output .= '<div class="comments_num"><a href="'.esc_url( get_the_permalink() ).'#respond"><i class="fa fa-comment"></i>'.get_comments_number().'</a></div>';
				}
				$output .= '</div>';

				$output .= '</article>';
			}

		}

	    $output .= '</div>';

	    wp_reset_query();


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


if( defined( 'WPB_VC_VERSION' ) ){
	new VCPostsClass();
}