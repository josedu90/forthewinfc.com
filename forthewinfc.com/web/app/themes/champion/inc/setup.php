<?php

if ( ! isset( $content_width ) ) {
	$content_width = 940;
}

add_action( 'after_setup_theme', 'local_theme_setup' );

function local_theme_setup() {

	add_theme_support( 'post-thumbnails' );

	add_theme_support( 'custom-header' );

	add_theme_support( 'custom-background' );

    add_theme_support( 'sportspress' );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'title-tag' );

	remove_action( 'wp_head', 'wp_generator' );

	add_image_size( 'blog_list', 270, 220, true );
	add_image_size( 'gallery_thumbnail', 80, 80, true );
	add_image_size( 'gallery_image', 560, 367, true );
	add_image_size( 'gallery_image_mini', 143, 116, true );
	add_image_size( 'player_photo', 740, 740, true );
	add_image_size( 'team_logo', 98, 98, false );

	load_theme_textdomain( 'champion', get_template_directory() . '/languages' );

	register_nav_menus(
		array(
			'primary'   => __( 'Top primary menu', 'champion' ),
			'secondary' => __( 'Secondary menu header', 'champion' ),
			'footer_menu' => __( 'Footer menu', 'champion' ),
		)
	);

	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery',
	) );

	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	register_sidebar(
		array(
			'name'          => __( 'Primary Sidebar', 'champion' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Main sidebar that appears on the right.', 'champion' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<div class="widget_title">',
			'after_title'   => '</div>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Shop Sidebar', 'champion' ),
			'id'            => 'shop',
			'description'   => __( 'Shop sidebar that appears on the right.', 'champion' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<div class="widget_title">',
			'after_title'   => '</div>',
		)
	);

    register_sidebar(
        array(
            'name'          => __( 'Sport Sidebar', 'champion' ),
            'id'            => 'sport',
            'description'   => __( 'Sport sidebar that appears on the right.', 'champion' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<div class="widget_title">',
            'after_title'   => '</div>',
        )
    );

	register_sidebar(
		array(
			'name'          => __( 'Footer area', 'champion' ),
			'id'            => 'footer',
			'description'   => __( 'Footer widget area that appears at the bottom.', 'champion' ),
			'before_widget' => '<aside id="%1$s" class="widget footer_widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<div class="widget_title">',
			'after_title'   => '</div>',
		)
	);

}

add_action( 'vc_before_init', 'stm_vcSetAsTheme' );

function stm_vcSetAsTheme() {
    vc_set_as_theme( true );
}

if ( ! function_exists( '_wp_render_title_tag' ) ) {
	function stm_slug_render_title() {		
		return '<title>' . wp_title( '|', false, 'right' ) . '</title>';
	}
	add_action( 'wp_head', 'stm_slug_render_title' );
}

function stm_remove_widgets() {
	unregister_widget( 'SP_Widget_Countdown' );
	unregister_widget( 'SP_Widget_Event_Calendar' );
	unregister_widget( 'SP_Widget_Event_List' );
	unregister_widget( 'SP_Widget_League_Table' );
	unregister_widget( 'SP_Widget_Player_Gallery' );
	unregister_widget( 'SP_Widget_Player_list' );
	unregister_widget( 'SP_Widget_Staff' );
	unregister_widget( 'WC_Widget_Layered_Nav_Filters' );
	unregister_widget( 'WC_Widget_Layered_Nav' );
	unregister_widget( 'WC_Widget_Product_Search' );
	unregister_widget( 'WC_Widget_Product_Tag_Cloud' );

	/*unregister_widget( 'WC_Widget_Top_Rated_Products' );
	unregister_widget( 'WC_Widget_Recent_Reviews' );
	unregister_widget( 'WC_Widget_Recently_Viewed' );
	unregister_widget( 'WC_Widget_Products' );
	unregister_widget( 'WC_Widget_Price_Filter' );
	unregister_widget( 'WC_Widget_Cart' );
	unregister_widget( 'WC_Widget_Product_Categories' );*/
}

add_action( 'widgets_init', 'stm_remove_widgets' );