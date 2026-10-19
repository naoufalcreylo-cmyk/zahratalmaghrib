<?php
/**
 * Theme supports, assets and menu locations.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports and text domain.
 */
function zam_setup() {
	load_theme_textdomain( 'zahrat-almaghrib', ZAM_DIR . '/languages' );

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'search-form' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 106,
			'width'       => 323,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Core's remote pattern directory adds generic patterns that do not fit the magazine.
	remove_theme_support( 'core-block-patterns' );

	add_editor_style( 'assets/css/theme.css' );

	// Classic locations keep Appearance → Menus available for the footer list.
	register_nav_menus(
		array(
			'footer' => __( 'Footer links', 'zahrat-almaghrib' ),
		)
	);
}
add_action( 'after_setup_theme', 'zam_setup' );

/**
 * Front-end styles and the single small script.
 */
function zam_enqueue_assets() {
	wp_enqueue_style( 'zam-theme', ZAM_URI . '/assets/css/theme.css', array(), ZAM_VERSION );

	if ( is_singular() ) {
		wp_enqueue_script(
			'zam-theme',
			ZAM_URI . '/assets/js/theme.js',
			array(),
			ZAM_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_localize_script(
			'zam-theme',
			'zamL10n',
			array(
				'copied' => __( 'Link copied', 'zahrat-almaghrib' ),
				'failed' => __( 'Copy failed. Select the address bar to copy the link.', 'zahrat-almaghrib' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'zam_enqueue_assets' );

/**
 * Preload the two Arabic font files every page needs above the fold.
 */
function zam_preload_fonts() {
	$fonts = array(
		'noto-kufi-arabic-arabic-wght-normal.woff2',
		'noto-naskh-arabic-arabic-wght-normal.woff2',
	);
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( ZAM_URI . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'zam_preload_fonts', 2 );

/**
 * Register the shared editor script used by the theme's blocks.
 */
function zam_register_editor_assets() {
	wp_register_script(
		'zam-blocks-editor',
		ZAM_URI . '/assets/js/editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render', 'wp-api-fetch', 'wp-data', 'wp-core-data', 'wp-url' ),
		ZAM_VERSION,
		true
	);
	wp_set_script_translations( 'zam-blocks-editor', 'zahrat-almaghrib', ZAM_DIR . '/languages' );
}
add_action( 'init', 'zam_register_editor_assets', 5 );

/**
 * Pattern category for the theme's insertable sections.
 */
function zam_register_pattern_category() {
	register_block_pattern_category(
		'zahrat-almaghrib',
		array( 'label' => __( 'Zahrat Al Maghrib', 'zahrat-almaghrib' ) )
	);
}
add_action( 'init', 'zam_register_pattern_category' );

/**
 * Styles for the block settings sidebar (outside the editor canvas).
 */
function zam_editor_ui_styles() {
	wp_enqueue_style( 'zam-editor-ui', ZAM_URI . '/assets/css/editor-ui.css', array(), ZAM_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'zam_editor_ui_styles' );
