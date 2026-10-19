<?php
/**
 * Zahrat Al Maghrib theme bootstrap.
 *
 * The theme only renders content. It never creates, edits or deletes posts,
 * terms, menus or options on activation.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

define( 'ZAM_VERSION', '1.0.0' );
define( 'ZAM_DIR', get_template_directory() );
define( 'ZAM_URI', get_template_directory_uri() );

require ZAM_DIR . '/inc/setup.php';
require ZAM_DIR . '/inc/helpers.php';
require ZAM_DIR . '/inc/cards.php';
require ZAM_DIR . '/inc/content.php';
require ZAM_DIR . '/inc/blocks.php';
