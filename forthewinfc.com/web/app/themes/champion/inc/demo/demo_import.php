<?php

function stm_admin_init() {
	global $pagenow;
	if ( is_admin() && $pagenow == 'themes.php' && isset( $_GET['activated'] ) ) {
		wp_redirect( admin_url() . 'themes.php?page=tgmpa-install-plugins' );
		exit;
	}
}

add_action('admin_init','stm_admin_init');

function demo_import_styles() {
	wp_enqueue_style( 'admin-style', get_template_directory_uri() . '/inc/demo/assets/css/demo_import.css', null, ( WP_DEBUG ) ? time() : null, 'all' );
}

add_action( 'admin_enqueue_scripts', 'demo_import_styles' );

add_action('admin_menu', 'stm_add_demo_import_page');

if ( ! function_exists('stm_add_demo_import_page'))
{
    function stm_add_demo_import_page()
    {
        add_submenu_page( 'themes.php' , 'Demo Import' , 'Demo Import' , 'manage_options' , 'stm_demo_import' , 'stm_demo_import' );
    }
}

if ( !function_exists('stm_demo_import'))
{
    function stm_demo_import()
    {
        ?>
		<div class="stm_message content" style="display:none;">
			<img src="<?php echo get_template_directory_uri(); ?>/inc/demo/assets/images/spinner.gif" alt="spinner">
			<h1 class="stm_message_title"><?php _e('Importing Demo Content...', 'champion'); ?></h1>
			<p class="stm_message_text"><?php _e('Duration of demo content importing depends on your server speed.', 'champion'); ?></p>
		</div>

        <div class="stm_message success" style="display:none;">
			<p class="stm_message_text"><?php echo sprintf(__('Congratulations and enjoy <a href="%s" target="_blank">your website</a> now!', 'champion'), home_url()); ?></p>
		</div>

		<form class="stm_importer" id="import_demo_data_form" action="?page=stm_demo_import" method="post">
			
			<div class="stm_importer_options">

				<div class="stm_importer_note">
					<strong><?php _e('Before installing the demo content, please NOTE:', 'champion'); ?></strong>
					<p><?php echo sprintf(__('Install the demo content only on a clean WordPress. Use <a href="%s" target="_blank">Wordpress Database Reset</a> plugin to clean the current Theme.', 'champion'), 'http://wordpress.org/plugins/wordpress-database-reset/'); ?></p>
					<p><?php _e('Remember that you will NOT get the images from live demo due to copyright / license reason.', 'champion'); ?></p>
				</div>
				<input class="button-primary size_big" type="submit" value="Import" id="import_demo_data">
			
			</div>
			
        </form>
        <script>
            jQuery(document).ready(function() {
                jQuery('#import_demo_data_form').on('submit', function() {
                    jQuery("html, body").animate({
                        scrollTop: 0
                    }, {
                        duration: 300
                    });
                    jQuery('.stm_importer').slideUp(null, function(){
                        jQuery('.stm_message.content').slideDown();
                    });

                    // Importing Content
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {
                            action: 'stm_demo_import_content'
                        },
                        success: function(){

	                        jQuery('.stm_message.content').slideUp();
	                        jQuery('.stm_message.success').slideDown();

                        }
                    });
                    return false;
                });
            });
        </script>
        <?php
    }

    // Content Import
    function stm_demo_import_content(){
        set_time_limit(0);

        if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true);

        require_once(get_template_directory().'/inc/demo/wordpress-importer/wordpress-importer.php');

        $wp_import = new WP_Import();
        $wp_import->fetch_attachments = true;

        ob_start();
            $wp_import->import(get_template_directory().'/inc/demo/data/demo_content.xml');
        ob_end_clean();

        $locations = get_theme_mod('nav_menu_locations');
        $menus  = wp_get_nav_menus();

        if(!empty($menus))
        {
            foreach($menus as $menu)
            {
                if(is_object($menu) && $menu->name == 'Primary menu')
                {
                    $locations['primary'] = $menu->term_id;
                }
                if(is_object($menu) && $menu->name == 'Secondary menu')
                {
                    $locations['secondary'] = $menu->term_id;
	                $locations['footer_menu'] = $menu->term_id;
                }
            }
        }

        set_theme_mod('nav_menu_locations', $locations);
		set_theme_mod( 'shop_slider_slug', 'shop' );

	    $front_page = get_page_by_title( 'Home' );
	    if ( isset( $front_page->ID ) ) {
		    update_option( 'show_on_front', 'page' );
		    update_option( 'page_on_front', $front_page->ID );
	    }
	    $blog_page = get_page_by_title( 'Blog' );
	    if ( isset( $blog_page->ID ) ) {
		    update_option( 'page_for_posts', $blog_page->ID );
	    }

	    $shop_page = get_page_by_title( 'Shop' );
	    if ( isset( $shop_page->ID ) ) {
		    update_option( 'woocommerce_shop_page_id', $shop_page->ID );
	    }
	    $cart_page = get_page_by_title( 'Cart' );
	    if ( isset( $cart_page->ID ) ) {
		    update_option( 'woocommerce_cart_page_id', $cart_page->ID );
	    }
	    $checkout_page = get_page_by_title( 'Checkout' );
	    if ( isset( $checkout_page->ID ) ) {
		    update_option( 'woocommerce_checkout_page_id', $checkout_page->ID );
	    }
	    $account_page = get_page_by_title( 'My Account' );
	    if ( isset( $account_page->ID ) ) {
		    update_option( 'woocommerce_myaccount_page_id', $account_page->ID );
	    }

	    update_option('ts_vcsc_extend_settings_additionsRows', 1);
	    update_option('ts_vcsc_extend_settings_customPoststicker', 1);

	    set_theme_mod('shop_slider_slug', 'shop');

	    $skin = 'style_1.txt';

	    $backup_file_content = file_get_contents( get_template_directory_uri() . '/inc/demo/data/mega_main_menu/' . $skin );
	    if ( $backup_file_content !== false && ( $options_backup = json_decode( $backup_file_content, true ) ) ) {
		    if ( isset( $options_backup['last_modified'] ) ) {
			    $options_backup['last_modified'] = time() + 30;
			    set_theme_mod( 'old_header_type', '' );
			    update_option( 'mega_main_menu_options', $options_backup );
		    }
	    }
		
		if ( class_exists('RevSlider') ){
		
			ob_start();
			$_FILES["import_file"]["tmp_name"] = get_template_directory().'/inc/demo/data/revslider/main.zip';
	
	        $slider = new RevSlider();
	        $response = $slider->importSliderFromPost();

			$_FILES["import_file"]["tmp_name"] = get_template_directory().'/inc/demo/data/revslider/shop.zip';

			$slider = new RevSlider();
			$response = $slider->importSliderFromPost();
	
	        ob_end_clean();
        }
        
        

        echo 'done';
        die();

    }

    add_action('wp_ajax_stm_demo_import_content', 'stm_demo_import_content');

}
