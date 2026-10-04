<?php
/**
 * Server-side rendering for the Team block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$heading               = $attributes['heading'] ?? 'Meet Our Team';
$subheading            = $attributes['subheading'] ?? '';
$columns               = $attributes['columns'] ?? 3;
$background_color      = $attributes['backgroundColor'] ?? '#ffffff';
$text_color            = $attributes['textColor'] ?? '#1f2937';
$card_background_color = $attributes['cardBackgroundColor'] ?? '#f9fafb';
$accent_color          = $attributes['accentColor'] ?? '#3b82f6';
$padding_top           = $attributes['paddingTop'] ?? 'lg';
$padding_bottom        = $attributes['paddingBottom'] ?? 'lg';
$image_style           = $attributes['imageStyle'] ?? 'rounded';

// Get values from helpers.
$padding_top_value    = FlatWP_Blocks::get_padding_value( $padding_top );
$padding_bottom_value = FlatWP_Blocks::get_padding_value( $padding_bottom );

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--team',
	'flatwp-team--image-' . esc_attr( $image_style ),
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
	'--flatwp-team-card-bg: ' . esc_attr( $card_background_color ) . ';',
	'--flatwp-team-accent: ' . esc_attr( $accent_color ) . ';',
);

// Grid classes.
$grid_classes = array(
	'flatwp-team__grid',
	'flatwp-team__grid--' . esc_attr( $columns ) . '-cols',
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
		<?php if ( $heading || $subheading ) : ?>
			<div class="flatwp-team__header">
				<?php if ( $heading ) : ?>
					<h2 class="flatwp-team__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $subheading ) : ?>
					<p class="flatwp-team__subheading"><?php echo esc_html( $subheading ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
