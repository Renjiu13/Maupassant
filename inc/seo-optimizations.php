<?php
/**
 * SEO Optimizations
 * Improves search engine visibility and social sharing
 */

/**
 * Add meta description
 */
function maupassant_add_meta_description() {
	if ( is_singular() ) {
		global $post;
		$description = get_the_excerpt( $post );
		if ( empty( $description ) ) {
			$description = wp_trim_words( strip_tags( $post->post_content ), 30, '...' );
		}
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	} elseif ( is_home() || is_front_page() ) {
		$description = get_bloginfo( 'description' );
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		}
	} elseif ( is_category() ) {
		$description = category_description();
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( strip_tags( $description ) ) . '">' . "\n";
		}
	} elseif ( is_tag() ) {
		$description = tag_description();
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( strip_tags( $description ) ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'maupassant_add_meta_description', 1 );

/**
 * Add Open Graph tags
 */
function maupassant_add_open_graph_tags() {
	if ( is_singular() ) {
		global $post;
		
		// OG Title
		echo '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
		
		// OG Type
		echo '<meta property="og:type" content="article">' . "\n";
		
		// OG URL
		echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '">' . "\n";
		
		// OG Description
		$description = get_the_excerpt( $post );
		if ( empty( $description ) ) {
			$description = wp_trim_words( strip_tags( $post->post_content ), 30, '...' );
		}
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		
		// OG Image
		if ( has_post_thumbnail() ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
			if ( $thumbnail ) {
				echo '<meta property="og:image" content="' . esc_url( $thumbnail[0] ) . '">' . "\n";
				echo '<meta property="og:image:width" content="' . esc_attr( $thumbnail[1] ) . '">' . "\n";
				echo '<meta property="og:image:height" content="' . esc_attr( $thumbnail[2] ) . '">' . "\n";
			}
		}
		
		// OG Site Name
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		
		// Article Published Time
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		
		// Article Modified Time
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		
		// Article Author
		echo '<meta property="article:author" content="' . esc_attr( get_the_author() ) . '">' . "\n";
		
	} elseif ( is_home() || is_front_page() ) {
		echo '<meta property="og:title" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( home_url( '/' ) ) . '">' . "\n";
		
		$description = get_bloginfo( 'description' );
		if ( $description ) {
			echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'maupassant_add_open_graph_tags', 2 );

/**
 * Add Twitter Card tags
 */
function maupassant_add_twitter_card_tags() {
	if ( is_singular() ) {
		global $post;
		
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
		
		$description = get_the_excerpt( $post );
		if ( empty( $description ) ) {
			$description = wp_trim_words( strip_tags( $post->post_content ), 30, '...' );
		}
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
		
		if ( has_post_thumbnail() ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
			if ( $thumbnail ) {
				echo '<meta name="twitter:image" content="' . esc_url( $thumbnail[0] ) . '">' . "\n";
			}
		}
	}
}
add_action( 'wp_head', 'maupassant_add_twitter_card_tags', 3 );

/**
 * Add JSON-LD structured data
 */
function maupassant_add_json_ld_schema() {
	if ( is_singular( 'post' ) ) {
		global $post;
		
		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'BlogPosting',
			'headline'      => get_the_title(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => get_site_icon_url(),
				),
			),
		);
		
		// Add description
		$description = get_the_excerpt( $post );
		if ( empty( $description ) ) {
			$description = wp_trim_words( strip_tags( $post->post_content ), 30, '...' );
		}
		$schema['description'] = $description;
		
		// Add image
		if ( has_post_thumbnail() ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
			if ( $thumbnail ) {
				$schema['image'] = array(
					'@type'  => 'ImageObject',
					'url'    => $thumbnail[0],
					'width'  => $thumbnail[1],
					'height' => $thumbnail[2],
				);
			}
		}
		
		// Add main entity of page
		$schema['mainEntityOfPage'] = array(
			'@type' => 'WebPage',
			'@id'   => get_permalink(),
		);
		
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		
	} elseif ( is_home() || is_front_page() ) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
		);
		
		$description = get_bloginfo( 'description' );
		if ( $description ) {
			$schema['description'] = $description;
		}
		
		// Add search action
		$schema['potentialAction'] = array(
			'@type'       => 'SearchAction',
			'target'      => home_url( '/?s={search_term_string}' ),
			'query-input' => 'required name=search_term_string',
		);
		
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'maupassant_add_json_ld_schema', 4 );

/**
 * Add canonical URL
 */
function maupassant_add_canonical_url() {
	if ( is_singular() ) {
		echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";
	} elseif ( is_home() || is_front_page() ) {
		echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
	} elseif ( is_category() || is_tag() || is_tax() ) {
		echo '<link rel="canonical" href="' . esc_url( get_term_link( get_queried_object() ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'maupassant_add_canonical_url', 5 );

/**
 * Add robots meta tag
 */
function maupassant_add_robots_meta() {
	if ( is_search() || is_404() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	} elseif ( is_archive() && get_query_var( 'paged' ) > 1 ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}
add_action( 'wp_head', 'maupassant_add_robots_meta', 1 );

/**
 * Improve image alt attributes
 */
function maupassant_improve_image_alt( $attr, $attachment ) {
	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = get_the_title( $attachment->ID );
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'maupassant_improve_image_alt', 10, 2 );

/**
 * Add breadcrumbs schema
 */
function maupassant_breadcrumb_schema() {
	if ( ! is_front_page() ) {
		$items = array();
		$position = 1;
		
		// Home
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => __( 'Home', 'maupassant' ),
			'item'     => home_url( '/' ),
		);
		
		// Category
		if ( is_category() || is_single() ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$category = $categories[0];
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => $category->name,
					'item'     => get_category_link( $category->term_id ),
				);
			}
		}
		
		// Current page
		if ( is_single() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			);
		} elseif ( is_category() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => single_cat_title( '', false ),
			);
		}
		
		if ( ! empty( $items ) ) {
			$schema = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			);
			
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	}
}
add_action( 'wp_head', 'maupassant_breadcrumb_schema', 6 );

/**
 * Add sitemap link to robots.txt
 */
function maupassant_add_sitemap_to_robots( $output ) {
	$output .= "Sitemap: " . home_url( '/sitemap.xml' ) . "\n";
	return $output;
}
add_filter( 'robots_txt', 'maupassant_add_sitemap_to_robots' );

/**
 * Optimize permalink structure for SEO
 */
function maupassant_optimize_permalinks() {
	// Suggest better permalink structure
	if ( get_option( 'permalink_structure' ) === '' ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}
}
add_action( 'after_switch_theme', 'maupassant_optimize_permalinks' );
