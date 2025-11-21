<?php

if ( ! function_exists( 'maupassant_setup' ) ) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function maupassant_setup() {
		load_theme_textdomain( 'maupassant', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Let WordPress manage the document title.
		add_theme_support( 'title-tag' );

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Switch default core markup for search form, comment form, and comments to output valid HTML5.
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		) );

		// Enable support for Post Thumbnails on posts and pages.
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 800, 500, true );

		// Enable support for background image
		add_theme_support( 'custom-background' );

		// Enable support for Post Formats.
		add_theme_support( 'post-formats', array(
			'aside',
			'image',
			'video',
			'quote',
			'link',
			'gallery',
			'status',
			'audio',
			'chat',
		) );

		// This theme styles the visual editor to resemble the theme style.
		add_editor_style( 'css/editor-style.css' );

		// Use wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'maupassant' ),
		) );
	}
}

add_action( 'after_setup_theme', 'maupassant_setup' );

/**
 * Register widget area.
 */
function maupassant_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Widget Area', 'maupassant' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar.', 'maupassant' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}

add_action( 'widgets_init', 'maupassant_widgets_init' );

/**
 * Set main content width.
 */
function maupassant_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'maupassant_content_width', 800 );
}

add_action( 'after_setup_theme', 'maupassant_content_width', 0 );

/**
 * Enqueue scripts.
 */
function maupassant_enqueue_scripts() {
	// 确保jQuery被加载
	wp_enqueue_script('jquery');
	
	// 添加回到顶部脚本
	wp_enqueue_script('back-to-top', get_template_directory_uri() . '/js/back-to-top.js', array('jquery'), '1.1', true);
	
	// 复制代码按钮脚本
	wp_enqueue_script('copy-code', get_template_directory_uri() . '/js/copy-code.js', array(), '1.0', true);
	
	// 只在需要时加载评论相关脚本
	if ( is_singular() && comments_open() ) {
		// 评论回复脚本
		if ( get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		
		// 评论增强功能脚本
		wp_enqueue_script('comment-enhancements', get_template_directory_uri() . '/js/comment-enhancements.js', array('jquery'), '1.0', true);
	}
}

add_action( 'wp_enqueue_scripts', 'maupassant_enqueue_scripts' );

/**
 * Enqueue styles.
 */
function maupassant_enqueue_styles() {
	// 添加版本号以便缓存控制
	$theme_version = wp_get_theme()->get('Version');
	
	// Critical CSS - load first
	wp_enqueue_style( 'normalize', get_template_directory_uri() . '/css/normalize.css', array(), '8.0.1' );
	wp_enqueue_style( 'maupassant-style', get_stylesheet_uri(), array( 'normalize' ), $theme_version );
	
	// Additional styles
	wp_enqueue_style( 'back-to-top', get_template_directory_uri() . '/css/back-to-top.css', array(), '1.1' );
	wp_enqueue_style( 'maupassant-menu-fix', get_template_directory_uri() . '/css/menu-fix.css', array(), '1.1' );
	wp_enqueue_style( 'footer-fix', get_template_directory_uri() . '/css/footer-fix.css', array(), '1.1' );
	
	// 确保site-logo.css最后加载，优先级最高
	wp_enqueue_style( 'site-logo', get_template_directory_uri() . '/css/site-logo.css', array(), '1.1' );
}

add_action( 'wp_enqueue_scripts', 'maupassant_enqueue_styles' );

require get_template_directory() . '/inc/general-settings.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/comment-enhancements.php';
require get_template_directory() . '/inc/performance-optimizations.php';
require get_template_directory() . '/inc/seo-optimizations.php';
require get_template_directory() . '/inc/accessibility-improvements.php';
require get_template_directory() . '/inc/security-enhancements.php';

/**
 * 修改归档小工具的日期格式，使月份显示为两位数（例如：2025 年 06 月）
 * 用于确保归档小工具中的月份显示格式统一，实现视觉对齐效果
 */
function maupassant_custom_archive_widget_date_format($link_html) {
    // 使用正则表达式匹配归档链接中的日期格式
    // 匹配类似 "2025 年 6 月" 的格式
    $pattern = '/>(\d{4})\s*年\s*(\d{1,2})\s*月</';
    
    // 替换为统一格式 "yyyy 年 mm 月"，其中月份为两位数
    $replacement = function($matches) {
        $year = $matches[1];
        $month = sprintf('%02d', $matches[2]); // 格式化月份为两位数
        return '>' . $year . ' 年 ' . $month . ' 月<';
    };
    
    // 应用替换并返回修改后的HTML
    return preg_replace_callback($pattern, $replacement, $link_html);
}
add_filter('get_archives_link', 'maupassant_custom_archive_widget_date_format');

/**
 * 修改归档标题格式，使月份显示为两位数（例如：2025 年 06 月）
 * 用于文章归档页面的标题显示格式统一
 */
function maupassant_custom_archive_title($title) {
    if (is_month()) {
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        // 格式化月份为两位数
        $formatted_month = sprintf('%02d', $month);
        $title = sprintf('%d 年 %s 月', $year, $formatted_month);
    }
    return $title;
}
add_filter('get_the_archive_title', 'maupassant_custom_archive_title');
