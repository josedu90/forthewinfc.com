<?php

class Stm_Instagram_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		require_once get_template_directory() . "/inc/lib/instagram.class.php";
		parent::__construct(
			'instagram', // Base ID
			__( 'Instagram', 'champion' ), // Name
			array( 'description' => __( 'Instagram widget', 'champion' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		if ( ! empty( $instance['clientId'] ) ) {

			$instagram = $this->connect( $instance );
			if($instance['instagramToken']){
				$instagram->setAccessToken( $instance['instagramToken'] );
				if(get_transient('instagramm_photos') == null){
					set_transient('instagramm_photos', $instagram->getUserMedia( 'self', $instance['posts'] ), 60 * 60 * $instance['cache']);
				}
				$media = get_transient('instagramm_photos');
				if ( ! empty( $media ) ) {
					echo '<ul>';
					foreach ( $media->data as $image ) {
						$src = $image->images->thumbnail->url;
						echo '<li><a href="' . esc_url( $image->link ) . '" target="blank"><img src="' . esc_url( $src ) . '" alt="" /></a></li>';
					}
					echo '</ul>';
				}
			}else{
				_e( 'Error Instagram Token', 'champion' );
			}

		} else {
			_e( 'Error Client Id', 'champion' );
		}


		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		if ( empty( $instance['clientId'] ) ) {
			$instance['clientId'] = '';
		}

		if ( empty( $instance['secret'] ) ) {
			$instance['secret'] = '';
		}

		$instagram = self::connect( $instance );

		$posts    = 9;
		$secret   = '';
		$clientId = '';
		$cache = 1;

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Instagram', 'champion' );
		}

		if ( isset( $instance['posts'] ) ) {
			$posts = $instance['posts'];
		}

		if ( isset( $instance['secret'] ) ) {
			$secret = $instance['secret'];
		}

        if ( isset( $instance['cache'] ) ) {
			$cache = $instance['cache'];
		}

		if ( isset( $instance['secret'] ) ) {
			$secret = $instance['secret'];
		}

		if ( ! empty( $_REQUEST['code'] ) && ! empty( $_REQUEST['instagramToken'] ) ) {
			$token = $instagram->getOAuthToken( $_REQUEST['code'] );
			$token = $token->access_token;
			set_transient( 'instagramToken', $token );
		} else {
			if(isset($instance['instagramToken'])){
				$token = $instance['instagramToken'];
			}else{
				$token = '';
			}
		}

		if ( isset( $instance['clientId'] ) ) {
			$clientId = $instance['clientId'];
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'champion' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posts' ) ); ?>"><?php _e( 'Number of posts to show:', 'champion' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts' ) ); ?>" type="text" value="<?php echo esc_attr( $posts ); ?>">
		</p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'cache' ) ); ?>"><?php _e( 'Cache: (h)', 'champion' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'cache' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cache' ) ); ?>" type="text" value="<?php echo esc_attr( $cache ); ?>">
        </p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'clientId' ) ); ?>"><?php _e( 'Client Id:', 'champion' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'clientId' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'clientId' ) ); ?>" type="text" value="<?php echo esc_attr( $clientId ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'secret' ) ); ?>"><?php _e( 'Secret:', 'champion' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'secret' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'secret' ) ); ?>" type="text" value="<?php echo esc_attr( $secret ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'token' ) ); ?>"><?php _e( 'Token:', 'champion' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'instagramToken' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'instagramToken' ) ); ?>" type="text" value="<?php echo esc_attr( $token ); ?>">
			<a href="<?php echo esc_url( $instagram->getLoginUrl() ); ?>"> Request Token </a>
		</p>
	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['title']    = ( ! empty( $new_instance['title'] ) ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['posts']    = ( ! empty( $new_instance['posts'] ) ) ? esc_attr( $new_instance['posts'] ) : '';
		$instance['secret']   = ( ! empty( $new_instance['secret'] ) ) ? $new_instance['secret'] : '';
		$instance['instagramToken']    = ( ! empty( $new_instance['instagramToken'] ) ) ?  $new_instance['instagramToken'] : '';
		$instance['clientId'] = ( ! empty( $new_instance['clientId'] ) ) ? $new_instance['clientId'] : '';
		$instance['cache'] = ( ! empty( $new_instance['cache'] ) ) ? esc_attr( $new_instance['cache'] ) : '';

		return $instance;
	}

	protected function connect( $instance ) {
		return new Instagram( array(
			'apiKey'      => $instance['clientId'],
			'apiSecret'   => $instance['secret'],
			'apiCallback' => add_query_arg( array( 'instagramToken' => 1 ), get_admin_url( '', 'widgets.php' ) )
		) );
	}

}

function register_instagram_widget() {
	register_widget( 'Stm_Instagram_Widget' );
}

add_action( 'widgets_init', 'register_instagram_widget' );