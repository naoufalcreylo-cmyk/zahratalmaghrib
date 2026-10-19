<?php
/**
 * Classic menu location (Appearance → Menus). Prints nothing when unassigned.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$zam_location = sanitize_key( $attributes['location'] ?? 'footer' );
if ( ! has_nav_menu( $zam_location ) ) {
	echo zam_editor_notice( __( 'Assign a menu to this location in Appearance → Menus.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$zam_label = trim( (string) ( $attributes['label'] ?? '' ) );
if ( '' === $zam_label ) {
	$zam_label = __( 'Footer links', 'zahrat-almaghrib' );
}

$zam_menu = wp_nav_menu(
	array(
		'theme_location' => $zam_location,
		'container'      => false,
		'menu_class'     => 'zam-menu__list',
		'depth'          => 1,
		'fallback_cb'    => false,
		'echo'           => false,
	)
);
if ( ! $zam_menu ) {
	return;
}
?>
<nav <?php echo zam_wrapper( array( 'class' => 'zam-menu' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( $zam_label ); ?>">
	<?php echo $zam_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</nav>
