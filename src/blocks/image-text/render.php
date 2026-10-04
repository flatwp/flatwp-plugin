<?php
/**
 * Server-side rendering for the Image-Text block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$image_position      = $attributes['imagePosition'] ?? 'left';
$image_url           = $attributes['imageUrl'] ?? '';
$image_alt           = $attributes['imageAlt'] ?? '';
$heading             = $attributes['heading'] ?? 'Section Heading';
$content_text        = $attributes['content'] ?? '';
$button_text         = $attributes['buttonText'] ?? '';
$button_url          = $attributes['buttonUrl'] ?? '';
$background_color    = $attributes['backgroundColor'] ?? '#ffffff';
$text_color          = $attributes['textColor'] ?? '#1f2937';
$button_style        = $attributes['buttonStyle'] ?? 'solid';
$button_color        = $attributes['buttonColor'] ?? '#3b82f6';
$padding_top         = $attributes['paddingTop'] ?? 'lg';
$padding_bottom      = $attributes['paddingBottom'] ?? 'lg';
$vertical_alignment  = $attributes['verticalAlignment'] ?? 'center';
$image_ratio         = $attributes['imageRatio'] ?? 'auto';
$image_border_radius = $attributes['imageBorderRadius'] ?? 'md';

// Get values from helpers.
$padding_top_value    = FlatWP_Blocks::get_padding_value( $padding_top );
$padding_bottom_value = FlatWP_Blocks::get_padding_value( $padding_bottom );
$border_radius_value  = FlatWP_Blocks::get_border_radius( $image_border_radius );

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--image-text',
	'flatwp-image-text--' . esc_attr( $image_position ),
	'flatwp-image-text--align-' . esc_attr( $vertical_alignment ),
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
);

// Image styles.
$image_styles = array();
if ( 'none' !== $image_border_radius ) {
	$image_styles[] = 'border-radius: ' . esc_attr( $border_radius_value ) . ';';
}

// Image ratio class.
$image_classes = array( 'flatwp-image-text__image' );
if ( 'auto' !== $image_ratio ) {
	$image_classes[] = 'flatwp-image-text__image--ratio-' . str_replace( ':', '-', esc_attr( $image_ratio ) );
}

// Button classes and styles.
$button_classes = 'flatwp-button flatwp-button--' . esc_attr( $button_style );
$button_styles = '';
if ( 'solid' === $button_style ) {
	$button_styles = sprintf(
		'background-color: %s; border-color: %s;',
		esc_attr( $button_color ),
		esc_attr( $button_color )
	);
} elseif ( 'outline' === $button_style ) {
	$button_styles = sprintf(
		'color: %s; border-color: %s;',
		esc_attr( $button_color ),
		esc_attr( $button_color )
	);
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);

// Placeholder image.
$placeholder_image = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"%3E%3Crect fill="%23e5e7eb" width="600" height="400"/%3E%3Cpath fill="%239ca3af" d="M250 150h100v100h-100z"/%3E%3Ccircle fill="%239ca3af" cx="220" cy="130" r="30"/%3E%3Cpath fill="%236b7280" d="M180 280l70-80 50 40 80-100 70 140z"/%3E%3C/svg%3E';
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="flatwp-container">
		<div class="flatwp-image-text__grid">
			<div class="flatwp-image-text__media">
				<img
					src="<?php echo esc_url( $image_url ? $image_url : $placeholder_image ); ?>"
					alt="<?php echo esc_attr( $image_alt ); ?>"
					class="<?php echo esc_attr( implode( ' ', $image_classes ) ); ?>"
					style="<?php echo esc_attr( implode( ' ', $image_styles ) ); ?>"
				/>
			</div>

			<div class="flatwp-image-text__content">
				<?php if ( $heading ) : ?>
					<h2 class="flatwp-image-text__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $content_text ) : ?>
					<div class="flatwp-image-text__text">
						<?php echo wpautop( esc_html( $content_text ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $button_text && $button_url ) : ?>
					<div class="flatwp-image-text__action">
						<a href="<?php echo esc_url( $button_url ); ?>" class="<?php echo esc_attr( $button_classes ); ?>" style="<?php echo esc_attr( $button_styles ); ?>">
							<?php echo esc_html( $button_text ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
