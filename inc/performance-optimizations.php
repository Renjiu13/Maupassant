<?php
/**
 * Performance Optimizations
 * Improves site speed, caching, and resource loading
 * 
 * @package Maupassant
 * @version 1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preload critical resources
 */
function maupassant_preload_resources() {
	// Preload main stylesheet
	printf( '<link rel="preload" href="%s" as="style">' . "\n", esc_url( get_stylesheet_uri() ) );
	
	// Preload critical CSS files
	$critical_css = array(
		'normalize.css',
		'base.css',
		'layout.css',
		'header.css',
	);
	
	foreach ( $critical_css as $css_file ) {
		printf( '<link rel="preload" href="%s" as="style">' . "\n", esc_url( get_template_directory_uri() . '/css/' . $css_file ) );
	}
	
	// DNS prefetch for external resources
	echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
}
add_action( 'wp_head', 'maupassant_preload_resources', 1 );

/**
 * Defer non-critical CSS
 * DISABLED by default - can cause FOUC (Flash of Unstyled Content)
 */
function maupassant_defer_non_critical_css() {
	// Only enable if explicitly requested
	if ( ! apply_filters( 'maupassant_defer_non_critical_css', false ) ) {
		return;
	}
	?>
	<script>
	// Load non-critical CSS asynchronously
	(function() {
		function loadDeferredStyles() {
			var addStylesNode = document.getElementById("deferred-styles");
			if (addStylesNode) {
				var replacement = document.createElement("div");
				replacement.innerHTML = addStylesNode.textContent;
				document.body.appendChild(replacement);
				addStylesNode.parentElement.removeChild(addStylesNode);
			}
		}
		var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
			window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
		if (raf) {
			raf(function() { window.setTimeout(loadDeferredStyles, 0); });
		} else {
			window.addEventListener('load', loadDeferredStyles);
		}
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'maupassant_defer_non_critical_css', 1 );

/**
 * Add async/defer to scripts
 */
function maupassant_add_async_defer_attributes( $tag, $handle ) {
	// Scripts to defer
	$defer_scripts = array(
		'back-to-top',
		'copy-code',
		'comment-enhancements',
	);
	
	// Scripts to async
	$async_scripts = array();
	
	if ( in_array( $handle, $defer_scripts, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	
	if ( in_array( $handle, $async_scripts, true ) ) {
		return str_replace( ' src', ' async src', $tag );
	}
	
	return $tag;
}
add_filter( 'script_loader_tag', 'maupassant_add_async_defer_attributes', 10, 2 );

/**
 * Remove query strings from static resources
 */
function maupassant_remove_query_strings( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'script_loader_src', 'maupassant_remove_query_strings', 15, 1 );
add_filter( 'style_loader_src', 'maupassant_remove_query_strings', 15, 1 );

/**
 * Enable Gzip compression
 * Note: Only enable if your server doesn't already have Gzip enabled
 * DISABLED by default - use server-level Gzip configuration instead
 */
function maupassant_enable_gzip_compression() {
	// Disabled to prevent conflicts with server configuration and output buffering
	// To enable, add: add_filter( 'maupassant_enable_gzip', '__return_true' );
	if ( ! apply_filters( 'maupassant_enable_gzip', false ) ) {
		return;
	}
	
	// Check if we're not in admin and Gzip is not already enabled
	if ( ! is_admin() && ! ini_get( 'zlib.output_compression' ) && 'ob_gzhandler' !== ini_get( 'output_handler' ) ) {
		if ( extension_loaded( 'zlib' ) && ! headers_sent() ) {
			ini_set( 'zlib.output_compression', 'On' );
			ini_set( 'zlib.output_compression_level', '6' );
		}
	}
}
add_action( 'init', 'maupassant_enable_gzip_compression' );

/**
 * Add browser caching headers
 * Note: Disabled by default as it may conflict with caching plugins
 * Uncomment if you want to enable it
 */
/*
function maupassant_add_cache_headers() {
	if ( ! is_admin() && ! headers_sent() ) {
		header( 'Cache-Control: public, max-age=31536000' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 31536000 ) . ' GMT' );
	}
}
add_action( 'send_headers', 'maupassant_add_cache_headers' );
*/

/**
 * Optimize database queries
 */
function maupassant_optimize_queries() {
	// Remove unnecessary queries
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	
	// Remove emoji scripts
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'maupassant_optimize_queries' );

/**
 * Lazy load images
 */
function maupassant_add_lazy_loading( $content ) {
	if ( is_feed() || is_preview() || empty( $content ) ) {
		return $content;
	}
	
	// Check if image already has loading attribute
	if ( strpos( $content, 'loading=' ) !== false ) {
		return $content;
	}
	
	// Add loading="lazy" to images that don't have it
	$content = preg_replace( '/<img((?![^>]*loading=)[^>]*)>/i', '<img$1 loading="lazy">', $content );
	
	return $content;
}
add_filter( 'the_content', 'maupassant_add_lazy_loading', 20 );
add_filter( 'post_thumbnail_html', 'maupassant_add_lazy_loading', 20 );

/**
 * Optimize post queries with caching
 */
function maupassant_cache_post_queries( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		// Cache query results
		$cache_key = 'main_query_' . md5( serialize( $query->query_vars ) );
		$cached_posts = wp_cache_get( $cache_key, 'posts' );
		
		if ( false === $cached_posts ) {
			// Cache will be set automatically by WordPress
			wp_cache_set( $cache_key, true, 'posts', 3600 );
		}
	}
}
add_action( 'pre_get_posts', 'maupassant_cache_post_queries' );

/**
 * Minify HTML output
 */
function maupassant_minify_html( $buffer ) {
	// Safety checks
	if ( empty( $buffer ) || is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return $buffer;
	}
	
	// Check if buffer is valid HTML
	if ( stripos( $buffer, '<html' ) === false ) {
		return $buffer;
	}
	
	// Protect pre, code, script, style, and textarea tags
	$protected = array();
	$buffer = preg_replace_callback(
		'/<(pre|code|script|style|textarea)[^>]*>.*?<\/\1>/is',
		function( $matches ) use ( &$protected ) {
			$placeholder = '<!--PROTECTED_' . count( $protected ) . '-->';
			$protected[] = $matches[0];
			return $placeholder;
		},
		$buffer
	);
	
	// Remove HTML comments (except IE conditionals)
	$buffer = preg_replace( '/<!--(?!\s*(?:\[if [^\]]+]|<!|>|PROTECTED))(?:(?!-->).)*-->/s', '', $buffer );
	
	// Remove whitespace (but preserve single spaces)
	$buffer = preg_replace( '/\s+/', ' ', $buffer );
	$buffer = preg_replace( '/>\s+</', '><', $buffer );
	
	// Restore protected content
	foreach ( $protected as $index => $content ) {
		$buffer = str_replace( '<!--PROTECTED_' . $index . '-->', $content, $buffer );
	}
	
	return trim( $buffer );
}

/**
 * Enable HTML minification (optional - can be enabled via filter)
 */
function maupassant_enable_html_minification() {
	// Only enable if explicitly requested via filter
	if ( ! apply_filters( 'maupassant_enable_html_minification', false ) ) {
		return;
	}
	
	// Check if output buffering is already active
	if ( ob_get_level() > 0 ) {
		return;
	}
	
	// Check if headers have been sent
	if ( headers_sent() ) {
		return;
	}
	
	ob_start( 'maupassant_minify_html' );
}
add_action( 'template_redirect', 'maupassant_enable_html_minification', 1 );

/**
 * Optimize excerpt length and more link
 */
function maupassant_custom_excerpt_length( $length ) {
	return apply_filters( 'maupassant_excerpt_length', 55 );
}
add_filter( 'excerpt_length', 'maupassant_custom_excerpt_length', 999 );

function maupassant_custom_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'maupassant_custom_excerpt_more' );

/**
 * Disable embeds for better performance
 */
function maupassant_disable_embeds() {
	// Remove the REST API endpoint
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	
	// Turn off oEmbed auto discovery
	add_filter( 'embed_oembed_discover', '__return_false' );
	
	// Remove oEmbed-specific JavaScript
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'maupassant_disable_embeds', 9999 );

/**
 * Optimize heartbeat API
 */
function maupassant_optimize_heartbeat( $settings ) {
	// Only disable on frontend if explicitly enabled
	if ( ! is_admin() && apply_filters( 'maupassant_disable_frontend_heartbeat', true ) ) {
		wp_deregister_script( 'heartbeat' );
	} elseif ( is_admin() ) {
		// Slow down in admin
		$settings['interval'] = apply_filters( 'maupassant_heartbeat_interval', 60 );
	}
	return $settings;
}
add_filter( 'heartbeat_settings', 'maupassant_optimize_heartbeat' );

/**
 * Limit post revisions
 */
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 3 );
}

/**
 * Increase autosave interval
 */
if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
	define( 'AUTOSAVE_INTERVAL', 300 ); // 5 minutes
}

/**
 * Add resource hints
 */
function maupassant_resource_hints( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$urls[] = '//fonts.googleapis.com';
		$urls[] = '//fonts.gstatic.com';
	}
	
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => '//fonts.googleapis.com',
			'crossorigin',
		);
	}
	
	return $urls;
}
add_filter( 'wp_resource_hints', 'maupassant_resource_hints', 10, 2 );

/**
 * Optimize menu queries
 */
function maupassant_optimize_nav_menu( $args ) {
	$args['cache_key'] = 'nav_menu_' . md5( serialize( $args ) );
	return $args;
}
add_filter( 'wp_nav_menu_args', 'maupassant_optimize_nav_menu' );

/**
 * Clean up head
 */
function maupassant_cleanup_head() {
	// Remove unnecessary links
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'start_post_rel_link' );
	remove_action( 'wp_head', 'index_rel_link' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
}
add_action( 'init', 'maupassant_cleanup_head' );
