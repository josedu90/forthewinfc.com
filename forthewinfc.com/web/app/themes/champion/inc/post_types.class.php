<?php

add_action( 'init', array( 'ST_PostType', 'init' ) );

class ST_PostType {

	/* assign collectors */
	protected static $PostTypes = array();
	protected static $Metaboxes = array();
	protected static $Taxonomies = array();


	public static function init() {

		self::register_custom_post_types();
		self::register_taxonomies();
		self::register_metabox_fields();

		add_action( 'save_post', array( get_class(), 'save_metaboxes' ) );

		/* Create metaboxes */
		add_action( 'add_meta_boxes', array( get_class(), 'add_metaboxes' ) );


	}

	public static function registerPostType( $postType, $title, $args ) {

		$pluralTitle = empty( $args['pluralTitle'] ) ? $title . 's' : $args['pluralTitle'];
		$labels      = array(
			'name'               => __( $pluralTitle, 'champion' ),
			'singular_name'      => __( $title, 'champion' ),
			'add_new'            => __( 'Add New', 'champion' ),
			'add_new_item'       => __( 'Add New ' . $title, 'champion' ),
			'edit_item'          => __( 'Edit ' . $title, 'champion' ),
			'new_item'           => __( 'New ' . $title, 'champion' ),
			'all_items'          => __( 'All ' . $pluralTitle, 'champion' ),
			'view_item'          => __( 'View ' . $title, 'champion' ),
			'search_items'       => __( 'Search ' . $pluralTitle, 'champion' ),
			'not_found'          => __( 'No ' . $pluralTitle . ' found', 'champion' ),
			'not_found_in_trash' => __( 'No ' . $pluralTitle . '  found in Trash', 'champion' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( $pluralTitle, 'champion' )
		);

		$defaults = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'query_var'          => true,
			//'rewrite'            => array( 'slug' => 'book' ),
			//'capability_type'    => 'post',

			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor' ),
		);

		$args                       = wp_parse_args( $args, $defaults );
		self::$PostTypes[$postType] = $args;

	}


	public static function addMetaBox(
		$id, $title, $post_types, $callback = '', $context = '',
		$priority = '', $callback_args = ''
	) {

        foreach($post_types as $post_type){
            $title = empty( $title ) ? __( 'Options', 'champion' ) : $title;
            self::$Metaboxes[$post_type . '_' . $id] = array(
                'title'         => $title,
                'callback'      => $callback,
                'post_type'     => $post_type,
                'context'       => empty( $context ) ? 'normal' : $context,
                'priority'      => $priority,
                'callback_args' => $callback_args,
            );
        }

	}


	public static function register_custom_post_types() {
		foreach ( self::$PostTypes as $postType => $args ) {
			register_post_type( $postType, $args );
		}
	}

	public static function add_metaboxes() {


		foreach ( self::$Metaboxes as $boxId => $args ) {
			add_meta_box(
				$boxId,
				$args['title'],
				empty( $args['callback'] ) ? array( get_class(), 'display_metaboxes' ) : $args['callback'],
				$args['post_type'],
				$args['context'],
				$args['priority'],
				$args['callback_args']
			);
		}


	}

	public static function display_metaboxes( $post, $metabox ) {

		wp_nonce_field( plugin_basename( __FILE__ ), 'stm_custom_nonce' );

		$fields = $metabox['args']['fields'];
		sForms::init( $fields, 'stm_custom_data' );

		if ( ! empty( $post->ID ) ) {

			$customs = get_post_custom( $post->ID );
			$data    = array();
			if ( ! empty( $customs ) ) {
				foreach ( $customs as $key => $value ) {
					$data[$key] = $value[0];

					//						if(false !== unserialize( $data[ $key ] ) ) $data[$key] = unserialize($data[$key]) ;
				}
			}


			sForms::setData( $data );
		}

		$template = locate_template( 'inc/metaboxes/default.php' );
		if ( ! empty( $template ) ) {
			include( $template );
		}
	}


	public static function save_metaboxes( $post_id ) {


		// Check if our nonce is set.
		if ( ! isset( $_POST['stm_custom_nonce'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['stm_custom_nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}


		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}


		sForms::setData( $_POST['stm_custom_data'] );

		if ( sForms::validate() ) {

			foreach ( sForms::$data as $fieldName => $value ) {

				//if( ! is_array($value)) $value = sanitize_text_field( $value );
				update_post_meta( $post_id, $fieldName, $value );
			}

		} else {

			echo 'error';
		}

	}


	public static function addTaxonomy( $taxonomyName, $slug, $post_type, $args = '' ) {
		// Add new taxonomy, make it hierarchical (like categories)
		$pluralName = empty( $args['plural'] ) ? $taxonomyName . 's' : $args['plural'];
		$labels     = array(
			'name'              => _x( $taxonomyName, 'taxonomy general name', 'stm_theme' ),
			'singular_name'     => _x( $taxonomyName, 'taxonomy singular name', 'stm_theme' ),
			'search_items'      => __( 'Search ' . $pluralName, 'stm_theme' ),
			'all_items'         => __( 'All ' . $pluralName, 'stm_theme' ),
			'parent_item'       => __( 'Parent ' . $taxonomyName, 'stm_theme' ),
			'parent_item_colon' => __( 'Parent ' . $taxonomyName . ':', 'stm_theme' ),
			'edit_item'         => __( 'Edit ' . $taxonomyName, 'stm_theme' ),
			'update_item'       => __( 'Update ' . $taxonomyName, 'stm_theme' ),
			'add_new_item'      => __( 'Add New ' . $taxonomyName, 'stm_theme' ),
			'new_item_name'     => __( 'New ' . $taxonomyName . 'Name', 'stm_theme' ),
			'menu_name'         => __( $taxonomyName, 'stm_theme' ),
		);

		$defaults = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_in_nav_menus' => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $slug ),
		);

		$args                    = wp_parse_args( $defaults, $args );
		self::$Taxonomies[$slug] = array( 'post_type' => $post_type, 'args' => $args );
	}


	public static function register_taxonomies() {

		foreach ( self::$Taxonomies as $taxonomyName => $taxonomy ) {
			register_taxonomy( $taxonomyName, $taxonomy['post_type'], $taxonomy['args'] );

		}

	}

	public static function register_metabox_fields() {

		function stm_metabox_text( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
			echo '<td>'.sForms::textField( $args['fieldName'] ).'</td>';
		}

		add_action( 'stm_metabox_text', 'stm_metabox_text' );


		function stm_metabox_selectbox( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
			echo '<td>'.sForms::selectbox( $args['fieldName'], $args['options'] ).'</td>';
		}

		add_action( 'stm_metabox_selectbox', 'stm_metabox_selectbox' );

		function stm_metabox_radio( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
			echo '<td>'.sForms::radioList( $args['fieldName'], $args['options'] ).'</td>';
		}

		add_action( 'stm_metabox_radio', 'stm_metabox_radio' );

		function stm_metabox_textarea( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
			echo '<td>'.sForms::textarea( $args['fieldName'], array( 'html_options' => array( 'cols' => 60, 'rows' => 4, 'style' => "width:98%" ) ) ).'</td>';
		}

		add_action( 'stm_metabox_textarea', 'stm_metabox_textarea' );

		function stm_metabox_multiselectbox( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
			echo '<td>'.sForms::selectbox( $args['fieldName'], $args['options'], array( 'html_options' => array( 'multiple' => 'true' ) ) ).'</td>';
		}

		add_action( 'stm_metabox_multiselectbox', 'stm_metabox_multiselectbox' );

		function stm_metabox_datepicker( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
			echo '<td>'.sForms::textField( $args['fieldName'], array( 'html_options' => array( 'class' => 'datetimepicker' ) ) ).'</td>';
		}

		add_action( 'stm_metabox_datepicker', 'stm_metabox_datepicker' );

		function stm_metabox_image_select( $args ) {
			echo '<td>'.sForms::label( $args['fieldName'] ).'</td>';
            echo '<td>'.sForms::imageField( $args['fieldName'] ).'</td>';
		}

		add_action( 'stm_metabox_image_select', 'stm_metabox_image_select' );

	}

}