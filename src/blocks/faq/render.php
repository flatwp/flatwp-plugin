<?php
/**
 * Server-side rendering for the FAQ block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$heading               = $attributes['heading'] ?? 'Frequently Asked Questions';
$subheading            = $attributes['subheading'] ?? '';
$layout                = $attributes['layout'] ?? 'stacked';
$background_color      = $attributes['backgroundColor'] ?? '#ffffff';
$text_color            = $attributes['textColor'] ?? '#1f2937';
$item_background_color = $attributes['itemBackgroundColor'] ?? '#f9fafb';
$border_color          = $attributes['borderColor'] ?? '#e5e7eb';
$accent_color          = $attributes['accentColor'] ?? '#3b82f6';
$padding_top           = $attributes['paddingTop'] ?? 'lg';
$padding_bottom        = $attributes['paddingBottom'] ?? 'lg';
$border_radius         = $attributes['borderRadius'] ?? 'md';

// Get values from helpers.
$padding_top_value    = FlatWP_Blocks::get_padding_value( $padding_top );
$padding_bottom_value = FlatWP_Blocks::get_padding_value( $padding_bottom );
$border_radius_value  = FlatWP_Blocks::get_border_radius( $border_radius );

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--faq',
	'flatwp-faq--' . esc_attr( $layout ),
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

// Build inline styles.
$wrapper_styles = array(
	'background-color: ' . esc_attr( $background_color ) . ';',
	'color: ' . esc_attr( $text_color ) . ';',
	'padding-top: ' . esc_attr( $padding_top_value ) . ';',
	'padding-bottom: ' . esc_attr( $padding_bottom_value ) . ';',
	'--flatwp-faq-item-bg: ' . esc_attr( $item_background_color ) . ';',
	'--flatwp-faq-border: ' . esc_attr( $border_color ) . ';',
	'--flatwp-faq-accent: ' . esc_attr( $accent_color ) . ';',
	'--flatwp-faq-radius: ' . esc_attr( $border_radius_value ) . ';',
);

// Grid classes for two-column layout.
$items_classes = array( 'flatwp-faq__items' );
if ( 'two-column' === $layout ) {
	$items_classes[] = 'flatwp-faq__items--two-column';
}

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
		<?php if ( $heading || $subheading ) : ?>
			<div class="flatwp-faq__header">
				<?php if ( $heading ) : ?>
					<h2 class="flatwp-faq__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $subheading ) : ?>
					<p class="flatwp-faq__subheading"><?php echo esc_html( $subheading ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( implode( ' ', $items_classes ) ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
