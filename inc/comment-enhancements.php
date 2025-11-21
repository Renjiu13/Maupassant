<?php
/**
 * Comment Enhancements - Server-side optimizations
 * Provides security, performance, and functionality improvements for comments
 */

/**
 * Add honeypot field validation
 */
function maupassant_check_comment_honeypot( $commentdata ) {
	if ( isset( $_POST['comment-honeypot'] ) && ! empty( $_POST['comment-honeypot'] ) ) {
		wp_die( __( 'Spam detected.', 'maupassant' ), __( 'Comment Submission Failed', 'maupassant' ), array( 'response' => 403 ) );
	}
	return $commentdata;
}
add_filter( 'preprocess_comment', 'maupassant_check_comment_honeypot' );

/**
 * Rate limiting for comments
 * Prevents spam by limiting comment frequency per IP
 */
function maupassant_comment_rate_limit( $commentdata ) {
	$comment_flood_option = get_option( 'comment_flood_option', 60 );
	
	// Get user IP
	$user_ip = $_SERVER['REMOTE_ADDR'];
	
	// Check for recent comments from this IP
	$recent_comments = get_comments( array(
		'author__in' => array( $user_ip ),
		'date_query' => array(
			array(
				'after' => $comment_flood_option . ' seconds ago',
			),
		),
		'count' => true,
	) );
	
	// Allow logged-in users to bypass rate limit
	if ( ! is_user_logged_in() && $recent_comments > 0 ) {
		wp_die(
			sprintf(
				__( 'You are posting comments too quickly. Please wait %d seconds before posting again.', 'maupassant' ),
				$comment_flood_option
			),
			__( 'Slow down!', 'maupassant' ),
			array( 'response' => 429 )
		);
	}
	
	return $commentdata;
}
add_filter( 'preprocess_comment', 'maupassant_comment_rate_limit' );

/**
 * Enhanced comment content validation
 */
function maupassant_validate_comment_content( $commentdata ) {
	$comment_content = trim( $commentdata['comment_content'] );
	
	// Check minimum length
	if ( strlen( $comment_content ) < 5 ) {
		wp_die(
			__( 'Comment content must be at least 5 characters long.', 'maupassant' ),
			__( 'Comment Too Short', 'maupassant' ),
			array( 'response' => 400 )
		);
	}
	
	// Check maximum length
	if ( strlen( $comment_content ) > 1000 ) {
		wp_die(
			__( 'Comment content must not exceed 1000 characters.', 'maupassant' ),
			__( 'Comment Too Long', 'maupassant' ),
			array( 'response' => 400 )
		);
	}
	
	// Check for excessive links (common spam indicator)
	$link_count = preg_match_all( '/<a\s+href=/i', $comment_content );
	if ( $link_count > 3 ) {
		wp_die(
			__( 'Too many links in comment. Maximum 3 links allowed.', 'maupassant' ),
			__( 'Too Many Links', 'maupassant' ),
			array( 'response' => 400 )
		);
	}
	
	return $commentdata;
}
add_filter( 'preprocess_comment', 'maupassant_validate_comment_content' );

/**
 * Add CSRF token to comment form
 */
function maupassant_add_comment_csrf_field() {
	wp_nonce_field( 'comment_csrf_protection', 'comment_csrf_token', false );
}
add_action( 'comment_form', 'maupassant_add_comment_csrf_field' );

/**
 * Verify CSRF token on comment submission
 */
function maupassant_verify_comment_csrf( $commentdata ) {
	if ( ! isset( $_POST['comment_csrf_token'] ) || ! wp_verify_nonce( $_POST['comment_csrf_token'], 'comment_csrf_protection' ) ) {
		wp_die(
			__( 'Security check failed. Please refresh the page and try again.', 'maupassant' ),
			__( 'Security Error', 'maupassant' ),
			array( 'response' => 403 )
		);
	}
	return $commentdata;
}
add_filter( 'preprocess_comment', 'maupassant_verify_comment_csrf' );

/**
 * Optimize comment queries with caching
 */
function maupassant_cache_comment_queries( $comments, $post_id ) {
	$cache_key = 'comments_' . $post_id;
	$cached_comments = wp_cache_get( $cache_key, 'comments' );
	
	if ( false === $cached_comments ) {
		wp_cache_set( $cache_key, $comments, 'comments', 3600 ); // Cache for 1 hour
	}
	
	return $comments;
}
add_filter( 'comments_array', 'maupassant_cache_comment_queries', 10, 2 );

/**
 * Clear comment cache when new comment is added
 */
function maupassant_clear_comment_cache( $comment_id, $comment_approved ) {
	if ( 1 === $comment_approved ) {
		$comment = get_comment( $comment_id );
		$cache_key = 'comments_' . $comment->comment_post_ID;
		wp_cache_delete( $cache_key, 'comments' );
	}
}
add_action( 'comment_post', 'maupassant_clear_comment_cache', 10, 2 );

/**
 * Add loading="lazy" to comment avatars for better performance
 */
function maupassant_lazy_load_avatars( $avatar ) {
	return str_replace( '<img ', '<img loading="lazy" ', $avatar );
}
add_filter( 'get_avatar', 'maupassant_lazy_load_avatars' );

/**
 * Improve comment form accessibility
 */
function maupassant_improve_comment_form_fields( $fields ) {
	// Add aria-required and improve labels
	$fields['author'] = str_replace(
		'<input',
		'<input aria-required="true" placeholder="请输入您的姓名"',
		$fields['author']
	);
	
	$fields['email'] = str_replace(
		'<input',
		'<input aria-required="true" placeholder="请输入您的邮箱"',
		$fields['email']
	);
	
	if ( isset( $fields['url'] ) ) {
		$fields['url'] = str_replace(
			'<input',
			'<input placeholder="请输入您的网站（可选）"',
			$fields['url']
		);
	}
	
	return $fields;
}
add_filter( 'comment_form_default_fields', 'maupassant_improve_comment_form_fields' );

/**
 * Improve comment textarea
 */
function maupassant_improve_comment_textarea( $args ) {
	$args['comment_field'] = str_replace(
		'<textarea',
		'<textarea aria-required="true" placeholder="请输入您的评论内容..." maxlength="1000"',
		$args['comment_field']
	);
	
	return $args;
}
add_filter( 'comment_form_defaults', 'maupassant_improve_comment_textarea' );

/**
 * Add custom comment classes for better styling
 */
function maupassant_custom_comment_classes( $classes, $class, $comment_id ) {
	$comment = get_comment( $comment_id );
	
	// Add class for post author comments
	if ( $comment->user_id > 0 ) {
		$post = get_post( $comment->comment_post_ID );
		if ( $post && $comment->user_id === $post->post_author ) {
			$classes[] = 'comment-by-post-author';
		}
	}
	
	// Add class for even/odd comments
	static $comment_count = 0;
	$comment_count++;
	$classes[] = ( $comment_count % 2 === 0 ) ? 'comment-level-even' : 'comment-level-odd';
	
	return $classes;
}
add_filter( 'comment_class', 'maupassant_custom_comment_classes', 10, 3 );

/**
 * Sanitize comment content more thoroughly
 */
function maupassant_sanitize_comment_content( $comment_content ) {
	// Remove excessive whitespace
	$comment_content = preg_replace( '/\s+/', ' ', $comment_content );
	
	// Remove potentially dangerous HTML
	$allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array(),
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
		'code' => array(),
		'blockquote' => array(),
	);
	
	$comment_content = wp_kses( $comment_content, $allowed_tags );
	
	return $comment_content;
}
add_filter( 'pre_comment_content', 'maupassant_sanitize_comment_content' );

/**
 * Add comment moderation notice
 */
function maupassant_comment_moderation_notice( $comment_id, $comment_approved ) {
	if ( 0 === $comment_approved ) {
		add_filter( 'comment_post_redirect', function( $location ) {
			return add_query_arg( 'comment-moderation', '1', $location );
		} );
	}
}
add_action( 'comment_post', 'maupassant_comment_moderation_notice', 10, 2 );

/**
 * Display moderation notice
 */
function maupassant_display_moderation_notice() {
	if ( isset( $_GET['comment-moderation'] ) && '1' === $_GET['comment-moderation'] ) {
		echo '<div class="comment-message comment-message-info">';
		echo esc_html__( 'Your comment is awaiting moderation.', 'maupassant' );
		echo '</div>';
	}
}
add_action( 'comment_form_before', 'maupassant_display_moderation_notice' );

/**
 * Optimize comment pagination
 */
function maupassant_optimize_comment_pagination() {
	// Set default comments per page if not set
	if ( ! get_option( 'comments_per_page' ) ) {
		update_option( 'comments_per_page', 20 );
	}
	
	// Enable comment pagination by default
	if ( ! get_option( 'page_comments' ) ) {
		update_option( 'page_comments', 1 );
	}
}
add_action( 'after_setup_theme', 'maupassant_optimize_comment_pagination' );

/**
 * Add rel="nofollow" to comment links for SEO
 */
function maupassant_add_nofollow_to_comment_links( $text ) {
	return preg_replace_callback(
		'/<a\s+href=/i',
		function( $matches ) {
			return '<a rel="nofollow" href=';
		},
		$text
	);
}
add_filter( 'comment_text', 'maupassant_add_nofollow_to_comment_links' );

/**
 * Disable comment author links for non-logged-in users (spam prevention)
 */
function maupassant_disable_comment_author_links( $return, $author, $comment_id ) {
	$comment = get_comment( $comment_id );
	
	// Keep links for logged-in users
	if ( $comment->user_id > 0 ) {
		return $return;
	}
	
	// Remove link for non-logged-in users
	return $author;
}
add_filter( 'get_comment_author_link', 'maupassant_disable_comment_author_links', 10, 3 );
