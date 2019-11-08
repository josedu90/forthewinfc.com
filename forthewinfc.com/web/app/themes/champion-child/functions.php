<?php

if ( ! is_admin() ) {
    add_action( 'wp_enqueue_scripts', 'stm_enqueue_parent_styles' );
}

function stm_enqueue_parent_styles() {

    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );

}