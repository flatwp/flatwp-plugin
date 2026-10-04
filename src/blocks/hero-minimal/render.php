<?php
/**
 * Server-side rendering for the Hero Minimal block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$heading     = $attributes['heading'] ?? '';
$description = $attributes['description'] ?? '';
$alignment   = $attributes['alignment'] ?? 'center';
$cta_text    = $attributes['ctaText'] ?? '';
$cta_url     = $attributes['ctaUrl'] ?? '';

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--hero-minimal',
	'flatwp-hero-minimal--align-' . esc_attr( $alignment ),
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="flatwp-container">
		<div class="flatwp-hero-minimal__content">
			<?php if ( $heading ) : ?>
				<h1 class="flatwp-hero-minimal__heading"><?php echo wp_kses_post( $heading ); ?></h1>
			<?php endif; ?>

			<?php if ( $description ) : ?>
				<p class="flatwp-hero-minimal__description"><?php echo wp_kses_post( $description ); ?></p>
			<?php endif; ?>

			<?php if ( $cta_text && $cta_url ) : ?>
				<div class="flatwp-hero-minimal__cta">
					<a href="<?php echo esc_url( $cta_url ); ?>" class="flatwp-button flatwp-button--solid">
						<?php echo esc_html( $cta_text ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
