<h2 class="panel_title"><?php printf( __('<span>Add your review</span> for %s', 'champion'), get_the_title() ); ?></h2>
	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

		<div id="review_form_wrapper">
			<div id="review_form" class="product_review_form">
				<?php
					$commenter = wp_get_current_commenter();

					$comment_form = array(
						'title_reply'          => '',
						'title_reply_to'       => '',
						'title_reply_before'   => '',
						'title_reply_after'    => '',
						'comment_notes_after'  => '',
						'fields'               => array(
							'author' => '<div class="row"><div class="col-md-6 col-xs-12 col-sm-6"><div class="form-group">' .
										'<input class="form-control" id="author" name="author" placeholder="' . __( 'Your Name', 'woocommerce' ) . ' *" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></div></div>',
							'email'  => '<div class="col-md-6 col-xs-12 col-sm-6"><div class="form-group">' .
										'<input class="form-control" id="email" name="email" placeholder="' . __( 'Your Email', 'woocommerce' ) . ' *" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></div></div></div>',
						),
						'label_submit'  => __( 'Submit', 'woocommerce' ),
						'logged_in_as'  => '',
						'comment_field' => '',
					);

					$comment_form['comment_field'] = '<div class="form-group"><textarea id="comment" placeholder="' . __( 'Your Review *', 'champion' ) . '" class="form-control" name="comment" cols="45" rows="8" aria-required="true"></textarea></div>';

					if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
						$comment_form['comment_field'] .= '<div class="comment-form-rating form-group"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) .'</label><select name="rating" id="rating" aria-required="true" required>
							<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
							<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
							<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
							<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
							<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
							<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
						</select></div>';
					}

					comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>

	<?php else : ?>

		<p class="woocommerce-verification-required"><?php _e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>

	<?php endif; ?>
