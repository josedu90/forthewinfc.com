<?php
$args['list_tpl'] = '<ol class="breadcrumb">%s</ol>';
$args['text']     = array(
	'home'      => __( 'Home', 'champion' ),
	'category'  => __( 'Category archive "%s"', 'champion' ),
	'search'    => __( 'Search results for "%s"', 'champion' ),
	'tag'       => __( 'Tag "%s"', 'champion' ),
	'author'    => __( 'Author Archives: %s', 'champion' ),
	'not_found' => __( 'Page not found', 'champion' ),
	'paged'     => __( 'Page %s', 'champion' ),
	'blog'      => __( 'Blog', 'champion' ),
);

$breadcrumbs_status        = get_post_meta( get_the_ID(), 'breadcrumbs', true );
$global_breadcrumbs_status = get_theme_mod( 'breadcrumbs' );

if ( empty( $breadcrumbs_status ) && empty( $global_breadcrumbs_status ) ) {
	Stm_breadcrumbs::breadcrumbs( $args );
} elseif ( $breadcrumbs_status == 'default' && empty( $global_breadcrumbs_status ) ) {
	Stm_breadcrumbs::breadcrumbs( $args );
} elseif ( $breadcrumbs_status == 'show' ) {
	Stm_breadcrumbs::breadcrumbs( $args );
}