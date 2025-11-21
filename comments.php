<?php if ( post_password_required() ) {
	return;
} ?>

<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
        <h3 class="comments-title">
			<?php
			$comment_count = get_comments_number();
			if ( 1 === $comment_count ) {
				printf( esc_html__( 'One comment on &ldquo;%s&rdquo;', 'maupassant' ), get_the_title() );
			} else {
				printf(
					esc_html( _n( '%1$s comment', '%1$s comments', $comment_count, 'maupassant' ) ),
					number_format_i18n( $comment_count )
				);
			}
			?>
		</h3>

        <ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
				'type'        => 'comment',
			) );
			?>
        </ol>

		<?php
		maupassant_comments_pagination( array(
			'prev_text'          => '<span aria-hidden="true">&larr;</span> ' . __( 'Previous', 'maupassant' ),
			'next_text'          => __( 'Next', 'maupassant' ) . ' <span aria-hidden="true">&rarr;</span>',
			'screen_reader_text' => __( 'Comments navigation', 'maupassant' ),
			'type'               => 'list',
		) );
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'maupassant' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'id_form'              => 'comment-form',
		'class_form'           => 'comment-form',
		'title_reply'          => __( 'Leave a Comment', 'maupassant' ),
		'title_reply_to'       => __( 'Leave a Reply to %s', 'maupassant' ),
		'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
		'title_reply_after'    => '</h3>',
		'cancel_reply_before'  => '<span class="cancel-comment-reply">',
		'cancel_reply_after'   => '</span>',
		'cancel_reply_link'    => __( 'Cancel reply', 'maupassant' ),
		'label_submit'         => __( 'Post Comment', 'maupassant' ),
		'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
		'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
		'format'               => 'html5',
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . __( 'Comment', 'maupassant' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" class="textarea" cols="45" rows="8" required></textarea></p>',
		'must_log_in'          => '<p class="must-log-in">' . sprintf(
			__( 'You must be <a href="%s">logged in</a> to post a comment.', 'maupassant' ),
			wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
		) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf(
			__( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'maupassant' ),
			admin_url( 'profile.php' ),
			wp_get_current_user()->display_name,
			wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) )
		) . '</p>',
	) );
	?>
</div>
