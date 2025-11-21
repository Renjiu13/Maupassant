<?php
/**
 * Security Enhancements
 * Improves site security and protects against common vulnerabilities
 */

/**
 * Remove WordPress version from head
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Remove version from scripts and styles
 */
function maupassant_remove_wp_version_strings( $src ) {
	global $wp_version;
	parse_str( parse_url( $src, PHP_URL_QUERY ), $query );
	if ( ! empty( $query['ver'] ) && $query['ver'] === $wp_version ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'script_loader_src', 'maupassant_remove_wp_version_strings' );
add_filter( 'style_loader_src', 'maupassant_remove_wp_version_strings' );

/**
 * Add security headers
 */
function maupassant_add_security_headers() {
	if ( ! is_admin() ) {
		// Prevent clickjacking
		header( 'X-Frame-Options: SAMEORIGIN' );
		
		// Prevent MIME type sniffing
		header( 'X-Content-Type-Options: nosniff' );
		
		// Enable XSS protection
		header( 'X-XSS-Protection: 1; mode=block' );
		
		// Referrer policy
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		
		// Content Security Policy (basic)
		$csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;";
		header( "Content-Security-Policy: $csp" );
		
		// Permissions Policy
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	}
}
add_action( 'send_headers', 'maupassant_add_security_headers' );

/**
 * Disable XML-RPC
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove RSD link
 */
remove_action( 'wp_head', 'rsd_link' );

/**
 * Remove Windows Live Writer manifest link
 */
remove_action( 'wp_head', 'wlwmanifest_link' );

/**
 * Disable file editing in dashboard
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Hide login errors
 */
function maupassant_hide_login_errors() {
	return __( 'Invalid credentials. Please try again.', 'maupassant' );
}
add_filter( 'login_errors', 'maupassant_hide_login_errors' );

/**
 * Disable user enumeration
 */
function maupassant_disable_user_enumeration( $redirect, $request ) {
	if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) {
		wp_die( __( 'Forbidden', 'maupassant' ), 403 );
	}
	return $redirect;
}
add_filter( 'redirect_canonical', 'maupassant_disable_user_enumeration', 10, 2 );

/**
 * Sanitize file names on upload
 */
function maupassant_sanitize_file_name( $filename ) {
	$filename = preg_replace( '/[^a-zA-Z0-9._-]/', '', $filename );
	$filename = str_replace( ' ', '-', $filename );
	return strtolower( $filename );
}
add_filter( 'sanitize_file_name', 'maupassant_sanitize_file_name', 10 );

/**
 * Limit login attempts (basic implementation)
 */
function maupassant_check_login_attempts( $user, $password ) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_key = 'login_attempts_' . md5( $ip );
	$attempts = get_transient( $transient_key );
	
	if ( false === $attempts ) {
		$attempts = 0;
	}
	
	if ( $attempts >= 5 ) {
		return new WP_Error(
			'too_many_attempts',
			__( 'Too many login attempts. Please try again in 15 minutes.', 'maupassant' )
		);
	}
	
	return $user;
}
add_filter( 'authenticate', 'maupassant_check_login_attempts', 30, 2 );

/**
 * Track failed login attempts
 */
function maupassant_track_failed_login( $username ) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_key = 'login_attempts_' . md5( $ip );
	$attempts = get_transient( $transient_key );
	
	if ( false === $attempts ) {
		$attempts = 0;
	}
	
	$attempts++;
	set_transient( $transient_key, $attempts, 15 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'maupassant_track_failed_login' );

/**
 * Clear login attempts on successful login
 */
function maupassant_clear_login_attempts( $user_login, $user ) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_key = 'login_attempts_' . md5( $ip );
	delete_transient( $transient_key );
}
add_action( 'wp_login', 'maupassant_clear_login_attempts', 10, 2 );

/**
 * Add nonce to AJAX requests
 */
function maupassant_ajax_nonce() {
	wp_localize_script( 'jquery', 'maupassant_ajax', array(
		'nonce' => wp_create_nonce( 'maupassant_ajax_nonce' ),
		'url'   => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'maupassant_ajax_nonce' );

/**
 * Validate file uploads
 */
function maupassant_validate_file_upload( $file ) {
	// Check file size (max 5MB)
	$max_size = 5 * 1024 * 1024;
	if ( $file['size'] > $max_size ) {
		$file['error'] = __( 'File size exceeds maximum allowed size of 5MB.', 'maupassant' );
		return $file;
	}
	
	// Check file type
	$allowed_types = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx' );
	$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	
	if ( ! in_array( $file_ext, $allowed_types, true ) ) {
		$file['error'] = __( 'File type not allowed.', 'maupassant' );
		return $file;
	}
	
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'maupassant_validate_file_upload' );

/**
 * Disable directory browsing
 */
function maupassant_disable_directory_browsing() {
	$htaccess_file = ABSPATH . '.htaccess';
	if ( file_exists( $htaccess_file ) && is_writable( $htaccess_file ) ) {
		$htaccess_content = file_get_contents( $htaccess_file );
		if ( strpos( $htaccess_content, 'Options -Indexes' ) === false ) {
			$new_content = "# Disable directory browsing\nOptions -Indexes\n\n" . $htaccess_content;
			file_put_contents( $htaccess_file, $new_content );
		}
	}
}
add_action( 'admin_init', 'maupassant_disable_directory_browsing' );

/**
 * Secure cookies
 */
function maupassant_secure_cookies() {
	@ini_set( 'session.cookie_httponly', true );
	@ini_set( 'session.cookie_secure', true );
	@ini_set( 'session.use_only_cookies', true );
}
add_action( 'init', 'maupassant_secure_cookies', 1 );

/**
 * Add CSRF protection to forms
 */
function maupassant_add_csrf_token() {
	if ( ! is_admin() ) {
		wp_nonce_field( 'maupassant_csrf_protection', 'maupassant_csrf_token', false );
	}
}
add_action( 'wp_footer', 'maupassant_add_csrf_token' );

/**
 * Sanitize all user inputs
 */
function maupassant_sanitize_inputs( $input ) {
	if ( is_array( $input ) ) {
		return array_map( 'maupassant_sanitize_inputs', $input );
	}
	return sanitize_text_field( $input );
}

/**
 * Escape all outputs
 */
function maupassant_escape_output( $output ) {
	return esc_html( $output );
}

/**
 * Prevent hotlinking
 */
function maupassant_prevent_hotlinking() {
	$htaccess_file = ABSPATH . '.htaccess';
	if ( file_exists( $htaccess_file ) && is_writable( $htaccess_file ) ) {
		$htaccess_content = file_get_contents( $htaccess_file );
		if ( strpos( $htaccess_content, 'RewriteCond %{HTTP_REFERER}' ) === false ) {
			$domain = parse_url( home_url(), PHP_URL_HOST );
			$hotlink_protection = "\n# Prevent hotlinking\n";
			$hotlink_protection .= "RewriteEngine on\n";
			$hotlink_protection .= "RewriteCond %{HTTP_REFERER} !^$\n";
			$hotlink_protection .= "RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?{$domain} [NC]\n";
			$hotlink_protection .= "RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]\n\n";
			
			$new_content = $htaccess_content . $hotlink_protection;
			file_put_contents( $htaccess_file, $new_content );
		}
	}
}
add_action( 'admin_init', 'maupassant_prevent_hotlinking' );

/**
 * Add security audit log
 */
function maupassant_security_audit_log( $action, $details = '' ) {
	$log_entry = array(
		'timestamp' => current_time( 'mysql' ),
		'action'    => $action,
		'details'   => $details,
		'ip'        => $_SERVER['REMOTE_ADDR'],
		'user'      => is_user_logged_in() ? wp_get_current_user()->user_login : 'guest',
	);
	
	// Store in transient (for basic logging)
	$logs = get_transient( 'maupassant_security_logs' );
	if ( false === $logs ) {
		$logs = array();
	}
	
	$logs[] = $log_entry;
	
	// Keep only last 100 entries
	if ( count( $logs ) > 100 ) {
		$logs = array_slice( $logs, -100 );
	}
	
	set_transient( 'maupassant_security_logs', $logs, DAY_IN_SECONDS );
}

/**
 * Monitor suspicious activity
 */
function maupassant_monitor_suspicious_activity() {
	// Check for SQL injection attempts
	$suspicious_patterns = array(
		'/union.*select/i',
		'/select.*from/i',
		'/insert.*into/i',
		'/delete.*from/i',
		'/drop.*table/i',
		'/<script/i',
		'/javascript:/i',
	);
	
	$request_uri = $_SERVER['REQUEST_URI'];
	foreach ( $suspicious_patterns as $pattern ) {
		if ( preg_match( $pattern, $request_uri ) ) {
			maupassant_security_audit_log( 'suspicious_request', $request_uri );
			wp_die( __( 'Forbidden', 'maupassant' ), 403 );
		}
	}
}
add_action( 'init', 'maupassant_monitor_suspicious_activity', 1 );
