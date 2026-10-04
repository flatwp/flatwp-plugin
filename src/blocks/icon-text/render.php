<?php
/**
 * Server-side rendering for the Icon Text block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$media_type   = $attributes['mediaType'] ?? 'icon';
$icon         = $attributes['icon'] ?? 'star';
$image_url    = $attributes['imageUrl'] ?? '';
$image_alt    = $attributes['imageAlt'] ?? '';
$title        = $attributes['title'] ?? '';
$description  = $attributes['description'] ?? '';
$button_text  = $attributes['buttonText'] ?? '';
$button_url   = $attributes['buttonUrl'] ?? '';
$layout       = $attributes['layout'] ?? 'horizontal';
$media_size   = $attributes['mediaSize'] ?? 'medium';
$color_scheme = $attributes['colorScheme'] ?? 'light';
$icon_style   = $attributes['iconStyle'] ?? 'filled';

// Icon SVG paths.
$icons = array(
	'zap'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
	'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
	'rocket'   => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>',
	'target'   => '<circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle>',
	'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle>',
	'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
	'heart'    => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>',
	'settings' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>',
	'globe'    => '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
	'code'     => '<polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline>',
	'layers'   => '<polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline>',
);

$icon_path = isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['star'];
$icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icon_path . '</svg>';

// Color scheme styles.
$scheme_styles = array(
	'light'       => array( 'bg' => '#ffffff', 'text' => '#1f2937' ),
	'dark'        => array( 'bg' => '#111827', 'text' => '#f9fafb' ),
	'transparent' => array( 'bg' => 'transparent', 'text' => 'inherit' ),
);
$scheme = isset( $scheme_styles[ $color_scheme ] ) ? $scheme_styles[ $color_scheme ] : $scheme_styles['light'];

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--icon-text',
	'flatwp-icon-text--' . esc_attr( $layout ),
	'flatwp-icon-text--' . esc_attr( $color_scheme ),
	'flatwp-icon-text--media-' . esc_attr( $media_size ),
	'flatwp-icon-text--icon-style-' . esc_attr( $icon_style ),
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

// Build inline styles.
$wrapper_styles = array();
if ( 'transparent' !== $color_scheme ) {
	$wrapper_styles[] = 'background-color: ' . esc_attr( $scheme['bg'] ) . ';';
	$wrapper_styles[] = 'color: ' . esc_attr( $scheme['text'] ) . ';';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="flatwp-icon-text__media">
		<?php if ( 'icon' === $media_type ) : ?>
			<div class="flatwp-icon-text__icon">
				<?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php elseif ( $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="flatwp-icon-text__image" />
		<?php endif; ?>
	</div>

	<div class="flatwp-icon-text__content">
		<?php if ( $title ) : ?>
			<h3 class="flatwp-icon-text__title"><?php echo wp_kses_post( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( $description ) : ?>
			<p class="flatwp-icon-text__description"><?php echo wp_kses_post( $description ); ?></p>
		<?php endif; ?>

		<?php if ( $button_text && $button_url ) : ?>
			<a href="<?php echo esc_url( $button_url ); ?>" class="flatwp-icon-text__button">
				<?php echo esc_html( $button_text ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</a>
		<?php endif; ?>
	</div>
</div>
