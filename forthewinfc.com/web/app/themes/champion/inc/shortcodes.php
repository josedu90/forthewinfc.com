<?php

/* Init shortcodes */
add_action( 'init', 'stm_shortcode_buttons' );
function stm_shortcode_buttons() {
	add_filter( "mce_external_plugins", "stm_add_buttons" );
	add_filter( 'mce_buttons', 'stm_register_buttons' );
}

function stm_add_buttons( $plugin_array ) {
	$plugin_array['stm'] = get_template_directory_uri() . '/inc/tinymce/shortcodes.js?' . time();

	return $plugin_array;
}

function stm_register_buttons( $buttons ) {
	array_push( $buttons, 'shortcodes' );

	return $buttons;
}