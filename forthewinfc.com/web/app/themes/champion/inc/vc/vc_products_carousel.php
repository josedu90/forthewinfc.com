<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class VCProductsCarouselClass {
	function __construct() {
		// We safely integrate with VC with this hook
		add_action( 'init', array( $this, 'integrateWithVC' ) );

		// Use this when creating a shortcode addon
		add_shortcode( 'stm_pc', array( $this, 'render' ) );

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

		if ( function_exists( "vc_map" ) ) {
			vc_map( array(
				"name"              => __( "Products Carousel", 'champion' ),
				"description"       => __( "Place Products Carousel", 'champion' ),
				"base"              => "stm_pc",
				"class"             => "latest_products_carousel",
				"controls"          => "full",
				"icon"              => 'vc_products_carousel_icon',
				"category"          => __( 'STM', 'champion' ),
				'admin_enqueue_css' => array( get_template_directory_uri() . '/inc/vc/assets/css/vc_products_carousel_admin.css' ),
				// This will load css file in the VC backend editor
				"params"            => array(

					array(
						"type"        => "dropdown",
						"heading"     => __( "Product Selection", 'champion' ),
						"param_name"  => "selection",
						"width"       => 150,
						"value"       => array(
							__( 'Recent Products', 'champion' )       => "recent_products",
							__( 'Featured Products', 'champion' )     => "featured_products",
							__( 'Top Rated Products', 'champion' )    => "top_rated_products",
							__( 'Products on Sale', 'champion' )      => "sale_products",
							__( 'Best Selling Products', 'champion' ) => "best_selling_products"
						),
						"description" => __( "Select which products should be shown in the slider.", 'champion' ),
						"admin_label" => true
					),
					array(
						"type"       => "number",
						"heading"    => __( "Total Number of Products", 'champion' ),
						"param_name" => "products_total",
						"value"      => 12,
						"min"        => 1
					),
					array(
						"type"        => "dropdown",
						"heading"     => __( "Retrieve Ordered By", 'champion' ),
						"param_name"  => "orderby",
						"value"       => array(
							__( "Date", 'champion' )       => 'date',
							__( "Title", 'champion' )      => 'title',
							__( "ID", 'champion' )         => 'id',
							__( "Menu Order", 'champion' ) => 'menu_order',
							__( "Random", 'champion' )     => 'rand',
						),
						"admin_label" => true,
						"description" => __( "Select in by which order criterium the products should be retrieved from WordPress.", 'champion' )
					),
					array(
						"type"        => "dropdown",
						"heading"     => __( "Retrieve Order", 'champion' ),
						"param_name"  => "order",
						"value"       => array(
							__( "Descending", 'champion' ) => 'desc',
							__( "Ascending", 'champion' )  => 'asc',
						),
						"admin_label" => true,
						"description" => __( "Select in which order direction the products should be retrieved from WordPress.", 'champion' )
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
			'selection'      => 'recent_products',
			'orderby'        => 'date',
			'order'          => 'desc',
			'products_total' => 12
		), $atts ) );

		global $product;
		global $woocommerce;

		$meta_query = '';
		if ( $selection == "recent_products" ) {
			$meta_query = WC()->query->get_meta_query();
		}
		// Featured Products
		if ( $selection == "featured_products" ) {
			$meta_query = array(
				array(
					'key'     => '_visibility',
					'value'   => array( 'catalog', 'visible' ),
					'compare' => 'IN'
				),
				array(
					'key'   => '_featured',
					'value' => 'yes'
				)
			);
		}
		// Top Rated Products
		/*if ( $selection == "top_rated_products" ) {
			add_filter( 'posts_clauses', array( WC()->query, 'order_by_rating_post_clauses' ) );
			$meta_query = WC()->query->get_meta_query();
		}*/
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $products_total,
			'orderby'             => $orderby,
			'order'               => $order,
			'paged'               => 1,
			'meta_query'          => $meta_query
		);

		if ( $selection == "sale_products" ) {
			$product_ids_on_sale = wc_get_product_ids_on_sale();
			$meta_query          = array();
			$meta_query[]        = $woocommerce->query->visibility_meta_query();
			$meta_query[]        = $woocommerce->query->stock_status_meta_query();
			$args['meta_query']  = $meta_query;
			$args['post__in']    = $product_ids_on_sale;
		}

		if ( $selection == "best_selling_products" ) {
			$args['meta_key']   = 'total_sales';
			$args['orderby']    = 'meta_value_num';
			$args['meta_query'] = array(
				array(
					'key'     => '_visibility',
					'value'   => array( 'catalog', 'visible' ),
					'compare' => 'IN'
				)
			);
		}

		$loop   = new WP_Query( $args );
		$output = '';
		$i      = 0;
		if ( $loop->have_posts() &&  ( 'WooCommerce' )) {

			$rand = mt_rand( 555, 9999 );

			$output .= '<div class="woocommerce"><ul id="owl-' . esc_attr( $rand ) . '" class="products owl-carousel owl-theme">';
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$i ++;
				$product_id = get_the_ID();
				$product    = new WC_Product( $product_id );
				$stock      = $product->is_in_stock() ? 'true' : 'false';
				$onsale     = $product->is_on_sale() ? 'true' : 'false';

				if ( $stock == "true" ) {

					$output .= '<li class="' . esc_attr( implode( ' ', get_post_class( array(), $product_id ) ) ) . '">';

					$output .= '<a href="' . esc_url( get_the_permalink( $product_id ) ) . '">';
					if ( $onsale == 'true' ) {
						$output .= '<span class="onsale"></span>';
					}
					$output .= woocommerce_get_product_thumbnail();
					$output .= '</a>';

					$output .= '<div class="product_header clearfix">';
					$output .= '<h3><a href="' . esc_url( get_the_permalink( $product_id ) ) . '">' . get_the_title( $product_id ) . '</a></h3>';
					$output .= '<a class="reviews" href="' . esc_url( get_the_permalink( $product_id ) ) . '#reviews">' . sprintf( __( '%s reviews', 'champion' ), $product->get_rating_count() ) . '</a>';
					$output .= '</div>';

					$output .= '<div class="product_info clearfix">';
					$categories = wp_get_post_terms( $product_id, 'product_cat' );
					if ( $categories ) {
						$output .= '<a href="' . esc_url( get_term_link( $categories[0] ) ) . '" class="category">' . $categories[0]->name . '</a>';
					}
					if ( $rating_html = wc_get_rating_html( $product->get_average_rating() ) ) {
						$output .= $rating_html;
					}

					$output .= '</div>';

					$output .= '<div class="product_footer clearfix">';
					$output .= sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s product_type_%s">%s</a>',
						esc_url( get_permalink( wc_get_page_id( 'shop' ) ) . '?add-to-cart=' . $product_id ),
						esc_attr( $product->get_id() ),
						esc_attr( $product->get_sku() ),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						esc_attr( $product->get_type() ), __( 'ADD', 'champion' ) . '<i class="fa fa-shopping-cart"></i>', $product );
					if ( $price_html = $product->get_price_html() ) {
						$output .= '<span class="price">' . $price_html . '</span>';
					}
					$output .= '</div>';

					$output .= '</li>';
				}
			}
			$output .= '</ul></div>';

			$output .= '
                <script>
                    jQuery(document).ready(function($) {
                        var owl = jQuery("#owl-' . $rand . '");

                        owl.owlCarousel({
                            navigation : true,
                            pagination : false,
                            itemsTablet: [600,3],
                            navigationText : ["<i class=\"fa fa-angle-left\"></i>","<i class=\"fa fa-angle-right\"></i>"]
                        });
                    });
                </script>
            ';

		} else {
			echo __( "No products could be found.", 'champion' );
		}

		wp_reset_postdata();
		wp_reset_query();


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

if ( is_plugin_active( 'sportspress/sportspress.php' ) || is_plugin_active( 'sportspress-pro/sportspress-pro.php' ) && defined( 'WPB_VC_VERSION' ) && class_exists( 'WooCommerce' ) ) {

	new VCProductsCarouselClass();

}