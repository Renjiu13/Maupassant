<?php
/**
 * Accessibility Improvements
 * Enhances site accessibility for all users
 */

/**
 * Add skip to content link
 */
function maupassant_add_skip_link() {
	echo '<a class="skip-link screen-reader-text" href="#main">' . esc_html__( 'Skip to content', 'maupassant' ) . '</a>';
}
add_action( 'wp_body_open', 'maupassant_add_skip_link', 1 );

/**
 * Add skip link styles
 */
function maupassant_skip_link_styles() {
	?>
	<style>
	.skip-link {
		position: absolute;
		top: -40px;
		left: 0;
		background: #272727;
		color: #fff;
		padding: 8px 15px;
		text-decoration: none;
		z-index: 100000;
	}
	.skip-link:focus {
		top: 0;
	}
	.screen-reader-text {
		clip: rect(1px, 1px, 1px, 1px);
		position: absolute !important;
		height: 1px;
		width: 1px;
		overflow: hidden;
		word-wrap: normal !important;
	}
	.screen-reader-text:focus {
		background-color: #f1f1f1;
		border-radius: 3px;
		box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
		clip: auto !important;
		color: #21759b;
		display: block;
		font-size: 14px;
		font-weight: bold;
		height: auto;
		left: 5px;
		line-height: normal;
		padding: 15px 23px 14px;
		text-decoration: none;
		top: 5px;
		width: auto;
		z-index: 100000;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'maupassant_skip_link_styles' );

/**
 * Improve navigation accessibility
 */
function maupassant_nav_menu_args( $args ) {
	$args['container_aria_label'] = __( 'Primary Navigation', 'maupassant' );
	return $args;
}
add_filter( 'wp_nav_menu_args', 'maupassant_nav_menu_args' );

/**
 * Add ARIA labels to search form
 */
function maupassant_search_form_aria() {
	$form = '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">
		<label>
			<span class="screen-reader-text">' . esc_html__( 'Search for:', 'maupassant' ) . '</span>
			<input type="search" class="search-field" placeholder="' . esc_attr__( 'Search...', 'maupassant' ) . '" value="' . get_search_query() . '" name="s" aria-label="' . esc_attr__( 'Search', 'maupassant' ) . '" />
		</label>
		<button type="submit" class="search-submit" aria-label="' . esc_attr__( 'Submit search', 'maupassant' ) . '">
			<span class="screen-reader-text">' . esc_html__( 'Search', 'maupassant' ) . '</span>
		</button>
	</form>';
	
	return $form;
}
add_filter( 'get_search_form', 'maupassant_search_form_aria' );

/**
 * Add ARIA labels to pagination
 */
function maupassant_pagination_aria( $args ) {
	$args['aria_label'] = __( 'Posts navigation', 'maupassant' );
	return $args;
}
add_filter( 'navigation_markup_template', 'maupassant_pagination_aria' );

/**
 * Improve image accessibility
 */
function maupassant_post_thumbnail_alt( $html, $post_id ) {
	$alt = get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true );
	if ( empty( $alt ) ) {
		$alt = get_the_title( $post_id );
		$html = str_replace( 'alt=""', 'alt="' . esc_attr( $alt ) . '"', $html );
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'maupassant_post_thumbnail_alt', 10, 2 );

/**
 * Add focus styles
 */
function maupassant_focus_styles() {
	?>
	<style>
	a:focus,
	button:focus,
	input:focus,
	textarea:focus,
	select:focus {
		outline: 2px solid #272727;
		outline-offset: 2px;
	}
	
	/* High contrast focus for better visibility */
	@media (prefers-contrast: high) {
		a:focus,
		button:focus,
		input:focus,
		textarea:focus,
		select:focus {
			outline: 3px solid #000;
			outline-offset: 3px;
		}
	}
	
	/* Respect user's motion preferences */
	@media (prefers-reduced-motion: reduce) {
		*,
		*::before,
		*::after {
			animation-duration: 0.01ms !important;
			animation-iteration-count: 1 !important;
			transition-duration: 0.01ms !important;
			scroll-behavior: auto !important;
		}
	}
	</style>
	<?php
}
add_action( 'wp_head', 'maupassant_focus_styles' );

/**
 * Add language attribute to HTML tag
 */
function maupassant_language_attributes( $output ) {
	return $output . ' lang="' . esc_attr( get_bloginfo( 'language' ) ) . '"';
}
add_filter( 'language_attributes', 'maupassant_language_attributes' );

/**
 * Improve table accessibility
 */
function maupassant_table_accessibility( $content ) {
	// Add scope to table headers
	$content = preg_replace( '/<th(?![^>]*scope)/', '<th scope="col"', $content );
	
	// Add role to tables
	$content = preg_replace( '/<table(?![^>]*role)/', '<table role="table"', $content );
	
	return $content;
}
add_filter( 'the_content', 'maupassant_table_accessibility', 30 );

/**
 * Add landmark roles
 */
function maupassant_add_landmark_roles() {
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Add main landmark
		var main = document.getElementById('main');
		if (main && !main.hasAttribute('role')) {
			main.setAttribute('role', 'main');
		}
		
		// Add navigation landmark
		var nav = document.getElementById('nav-menu');
		if (nav && !nav.hasAttribute('role')) {
			nav.setAttribute('role', 'navigation');
		}
		
		// Add complementary landmark to sidebar
		var sidebar = document.getElementById('sidebar');
		if (sidebar && !sidebar.hasAttribute('role')) {
			sidebar.setAttribute('role', 'complementary');
		}
		
		// Add contentinfo landmark to footer
		var footer = document.getElementById('footer');
		if (footer && !footer.hasAttribute('role')) {
			footer.setAttribute('role', 'contentinfo');
		}
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'maupassant_add_landmark_roles' );

/**
 * Improve heading hierarchy
 */
function maupassant_check_heading_hierarchy( $content ) {
	// This is a placeholder for heading hierarchy checking
	// In a real implementation, you would analyze and fix heading levels
	return $content;
}
add_filter( 'the_content', 'maupassant_check_heading_hierarchy', 40 );

/**
 * Add text resize support
 */
function maupassant_text_resize_support() {
	?>
	<style>
	/* Ensure text can be resized up to 200% */
	html {
		font-size: 100%;
	}
	
	body {
		font-size: 1rem;
	}
	
	/* Ensure minimum touch target size (44x44px) */
	a,
	button,
	input[type="submit"],
	input[type="button"],
	input[type="reset"] {
		min-height: 44px;
		min-width: 44px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}
	
	/* Exception for inline links */
	p a,
	li a {
		min-height: auto;
		min-width: auto;
		display: inline;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'maupassant_text_resize_support' );

/**
 * Add color contrast improvements
 */
function maupassant_color_contrast() {
	?>
	<style>
	/* Ensure sufficient color contrast */
	:root {
		--text-color: #222;
		--bg-color: #fff;
		--link-color: #0066cc;
		--link-hover-color: #004499;
	}
	
	/* High contrast mode support */
	@media (prefers-contrast: high) {
		:root {
			--text-color: #000;
			--bg-color: #fff;
			--link-color: #0000ff;
			--link-hover-color: #0000cc;
		}
	}
	
	/* Dark mode support */
	@media (prefers-color-scheme: dark) {
		:root {
			--text-color: #e0e0e0;
			--bg-color: #1a1a1a;
			--link-color: #66b3ff;
			--link-hover-color: #99ccff;
		}
	}
	</style>
	<?php
}
add_action( 'wp_head', 'maupassant_color_contrast' );

/**
 * Add keyboard navigation improvements
 */
function maupassant_keyboard_navigation() {
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Add keyboard navigation to menu
		var menuItems = document.querySelectorAll('#nav-menu a');
		menuItems.forEach(function(item) {
			item.addEventListener('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					this.click();
				}
			});
		});
		
		// Trap focus in modals (if any)
		var modals = document.querySelectorAll('[role="dialog"]');
		modals.forEach(function(modal) {
			var focusableElements = modal.querySelectorAll('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])');
			if (focusableElements.length > 0) {
				var firstElement = focusableElements[0];
				var lastElement = focusableElements[focusableElements.length - 1];
				
				modal.addEventListener('keydown', function(e) {
					if (e.key === 'Tab') {
						if (e.shiftKey && document.activeElement === firstElement) {
							e.preventDefault();
							lastElement.focus();
						} else if (!e.shiftKey && document.activeElement === lastElement) {
							e.preventDefault();
							firstElement.focus();
						}
					}
				});
			}
		});
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'maupassant_keyboard_navigation' );

/**
 * Add ARIA live regions for dynamic content
 */
function maupassant_aria_live_regions() {
	echo '<div id="aria-live-region" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>';
}
add_action( 'wp_footer', 'maupassant_aria_live_regions' );
