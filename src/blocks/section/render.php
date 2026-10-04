<?php
/**
 * Server-side rendering for the Section block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$color_scheme         = $attributes['colorScheme'] ?? 'transparent';
$background_type      = $attributes['backgroundType'] ?? 'color';
$background_image_url = $attributes['backgroundImageUrl'] ?? '';
$overlay_opacity      = $attributes['overlayOpacity'] ?? 50;
$gradient_from        = $attributes['gradientFrom'] ?? '#2563eb';
$gradient_to          = $attributes['gradientTo'] ?? '#7c3aed';
$gradient_direction   = $attributes['gradientDirection'] ?? 'to-br';
$padding_top          = $attributes['paddingTop'] ?? 'large';
$padding_bottom       = $attributes['paddingBottom'] ?? 'large';
$container_width      = $attributes['containerWidth'] ?? 'default';
$divider_top          = $attributes['dividerTop'] ?? 'none';
$divider_bottom       = $attributes['dividerBottom'] ?? 'none';

// Padding values.
$padding_sizes = array(
	'none'   => '0',
	'small'  => '2rem',
	'medium' => '4rem',
	'large'  => '6rem',
	'xlarge' => '10rem',
);
$padding_top_value = isset( $padding_sizes[ $padding_top ] ) ? $padding_sizes[ $padding_top ] : $padding_sizes['large'];
$padding_bottom_value = isset( $padding_sizes[ $padding_bottom ] ) ? $padding_sizes[ $padding_bottom ] : $padding_sizes['large'];

// Container width values.
$container_widths = array(
	'narrow'  => '48rem',
	'default' => '72rem',
	'wide'    => '90rem',
	'full'    => '100%',
);
$container_width_value = isset( $container_widths[ $container_width ] ) ? $container_widths[ $container_width ] : $container_widths['default'];

// Gradient direction values.
$gradient_directions = array(
	'to-r'  => 'to right',
	'to-l'  => 'to left',
	'to-b'  => 'to bottom',
	'to-t'  => 'to top',
	'to-br' => 'to bottom right',
	'to-bl' => 'to bottom left',
	'to-tr' => 'to top right',
	'to-tl' => 'to top left',
);
$gradient_dir = isset( $gradient_directions[ $gradient_direction ] ) ? $gradient_directions[ $gradient_direction ] : 'to bottom right';

// Color scheme styles.
$scheme_styles = array(
	'light'       => array( 'bg' => '#ffffff', 'text' => '#1f2937' ),
	'dark'        => array( 'bg' => '#111827', 'text' => '#f9fafb' ),
	'primary'     => array( 'bg' => '#3b82f6', 'text' => '#ffffff' ),
	'gradient'    => array( 'bg' => 'gradient', 'text' => '#ffffff' ),
	'transparent' => array( 'bg' => 'transparent', 'text' => 'inherit' ),
);
$scheme = isset( $scheme_styles[ $color_scheme ] ) ? $scheme_styles[ $color_scheme ] : $scheme_styles['transparent'];

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--section',
	'flatwp-section--' . esc_attr( $color_scheme ),
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

if ( 'none' !== $divider_top ) {
	$wrapper_classes[] = 'flatwp-section--has-divider-top';
}

if ( 'none' !== $divider_bottom ) {
	$wrapper_classes[] = 'flatwp-section--has-divider-bottom';
}

// Build inline styles.
$wrapper_styles = array(
	'padding-top: ' . esc_attr( $padding_top_value ) . ';',
	'padding-bottom: ' . esc_attr( $padding_bottom_value ) . ';',
);

if ( 'gradient' === $color_scheme || 'gradient' === $background_type ) {
	$wrapper_styles[] = sprintf(
		'background: linear-gradient(%s, %s, %s);',
		esc_attr( $gradient_dir ),
		esc_attr( $gradient_from ),
		esc_attr( $gradient_to )
	);
	$wrapper_styles[] = 'color: #ffffff;';
} elseif ( 'image' === $background_type && $background_image_url ) {
	$wrapper_styles[] = sprintf( 'background-image: url(%s);', esc_url( $background_image_url ) );
	$wrapper_styles[] = 'background-size: cover;';
	$wrapper_styles[] = 'background-position: center;';
	$wrapper_styles[] = 'position: relative;';
} elseif ( 'transparent' !== $color_scheme ) {
	$wrapper_styles[] = 'background-color: ' . esc_attr( $scheme['bg'] ) . ';';
	$wrapper_styles[] = 'color: ' . esc_attr( $scheme['text'] ) . ';';
}

// Container classes.
$container_classes = array( 'flatwp-section__container' );
if ( 'full' !== $container_width ) {
	$container_classes[] = 'flatwp-container';
	$container_classes[] = 'flatwp-container--' . esc_attr( $container_width );
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);

// Divider SVGs.
$dividers = array(
	'wave'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="currentColor"></path></svg>',
	'curve'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" fill="currentColor"></path></svg>',
	'triangle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M598.97 114.72L0 0 0 120 1200 120 1200 0 598.97 114.72z" fill="currentColor"></path></svg>',
	'slant'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" fill="currentColor"></path></svg>',
);
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( 'image' === $background_type && $background_image_url ) : ?>
		<div class="flatwp-section__overlay" style="opacity: <?php echo esc_attr( $overlay_opacity / 100 ); ?>;"></div>
	<?php endif; ?>

	<?php if ( 'none' !== $divider_top && isset( $dividers[ $divider_top ] ) ) : ?>
		<div class="flatwp-section__divider flatwp-section__divider--top">
			<?php echo $dividers[ $divider_top ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>" <?php echo 'full' !== $container_width ? 'style="max-width: ' . esc_attr( $container_width_value ) . '; margin: 0 auto;"' : ''; ?>>
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<?php if ( 'none' !== $divider_bottom && isset( $dividers[ $divider_bottom ] ) ) : ?>
		<div class="flatwp-section__divider flatwp-section__divider--bottom">
			<?php echo $dividers[ $divider_bottom ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</section>
