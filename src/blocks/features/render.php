<?php
/**
 * Server-side rendering for the Features block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$heading       = $attributes['heading'] ?? 'Everything you need';
$subheading    = $attributes['subheading'] ?? '';
$show_heading  = $attributes['showHeading'] ?? true;
$columns       = $attributes['columns'] ?? 3;
$layout        = $attributes['layout'] ?? 'grid';
$color_scheme  = $attributes['colorScheme'] ?? 'light';
$icon_style    = $attributes['iconStyle'] ?? 'filled';
$icon_position = $attributes['iconPosition'] ?? 'top';

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
	'flatwp-block--features',
	'flatwp-features--' . esc_attr( $layout ),
	'flatwp-features--' . esc_attr( $color_scheme ),
	'flatwp-features--icon-' . esc_attr( $icon_position ),
	'flatwp-features--icon-style-' . esc_attr( $icon_style ),
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

// Grid classes.
$grid_classes = array(
	'flatwp-features__grid',
	'flatwp-features__grid--' . esc_attr( $columns ) . '-cols',
);

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="flatwp-container">
		<?php if ( $show_heading && ( $heading || $subheading ) ) : ?>
			<div class="flatwp-features__header">
				<?php if ( $heading ) : ?>
					<h2 class="flatwp-features__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="flatwp-features__subheading"><?php echo wp_kses_post( $subheading ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
