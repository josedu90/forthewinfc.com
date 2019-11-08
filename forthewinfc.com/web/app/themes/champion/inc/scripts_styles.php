<?php

/*
	Scripts and Styles (SS)
*/

add_action( 'wp_enqueue_scripts', 'stm_load_theme_ss' );

function stm_load_theme_ss() {

	/* Styles */
	wp_enqueue_style( 'theme-style', get_stylesheet_uri(), null, ( WP_DEBUG ) ? time() : null, 'all' );

	/* Scripts */
	wp_enqueue_script( 'fixie', get_template_directory_uri() . '/assets/js/fix-ie-css-limit-standalone.js', null, ( WP_DEBUG ) ? time() : null, false );
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );
	wp_enqueue_script( 'select2', get_template_directory_uri() . '/assets/js/select2.min.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );
	wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/assets/js/jquery.fancybox.pack.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );
	wp_enqueue_script( 'nicescroll', get_template_directory_uri() . '/assets/js/jquery.nicescroll.min.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );
	wp_enqueue_script( 'theme-js', get_template_directory_uri() . '/assets/js/custom.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	
	if( get_theme_mod( 'frontend_customizer' ) ){
		wp_enqueue_style( 'frontend_customizer', get_template_directory_uri() . '/assets/css/frontend_customizer.css', null, ( WP_DEBUG ) ? time() : '1.2', 'all' );
		wp_enqueue_style( 'colorpicker', get_template_directory_uri() . '/assets/css/colorpicker.css', null, ( WP_DEBUG ) ? time() : null, 'all' );
		wp_enqueue_script( 'custom-colorpicker', get_template_directory_uri() . '/assets/js/colorpicker.js', array( 'jquery' ), ( WP_DEBUG ) ? time() : null, true );
	}

}


function admin_styles() {
	wp_enqueue_style( 'admin-style', get_template_directory_uri() . '/assets/css/admin.css', null, ( WP_DEBUG ) ? time() : null, 'all' );
	wp_enqueue_style( 'admin-font-awesome', get_template_directory_uri() . '/assets/css/font-awesome.min.css', null, ( WP_DEBUG ) ? time() : null, 'all' );
}

add_action( 'admin_enqueue_scripts', 'admin_styles' );