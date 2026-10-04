<?php
/**
 * Server-side rendering for the Pricing Column block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$plan_name        = $attributes['planName'] ?? 'Pro Plan';
$plan_description = $attributes['planDescription'] ?? '';
$price            = $attributes['price'] ?? '49';
$currency         = $attributes['currency'] ?? '$';
$period           = $attributes['period'] ?? '/month';
$features         = $attributes['features'] ?? array();
$button_text      = $attributes['buttonText'] ?? 'Get Started';
$button_url       = $attributes['buttonUrl'] ?? '#';
$featured         = $attributes['featured'] ?? false;
$featured_label   = $attributes['featuredLabel'] ?? 'Most Popular';
$background_color = $attributes['backgroundColor'] ?? '#ffffff';
$text_color       = $attributes['textColor'] ?? '#1f2937';
$accent_color     = $attributes['accentColor'] ?? '#3b82f6';
$border_color     = $attributes['borderColor'] ?? '#e5e7eb';
$border_radius    = $attributes['borderRadius'] ?? 'lg';
$shadow           = $attributes['shadow'] ?? 'md';

// Get values from helpers.
$border_radius_value = FlatWP_Blocks::get_border_radius( $border_radius );
$shadow_value        = FlatWP_Blocks::get_shadow( $shadow );

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--pricing-column',
);

if ( $featured ) {
	$wrapper_classes[] = 'flatwp-pricing-column--featured';
}

// Build inline styles.
$wrapper_styles = array(
	'background-color: ' . esc_attr( $background_color ) . ';',
	'color: ' . esc_attr( $text_color ) . ';',
	'border: 2px solid ' . esc_attr( $featured ? $accent_color : $border_color ) . ';',
	'border-radius: ' . esc_attr( $border_radius_value ) . ';',
	'box-shadow: ' . esc_attr( $shadow_value ) . ';',
	'--flatwp-pricing-accent: ' . esc_attr( $accent_color ) . ';',
);

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);
?>

<article <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $featured && $featured_label ) : ?>
		<div class="flatwp-pricing-column__badge" style="background-color: <?php echo esc_attr( $accent_color ); ?>;">
			<?php echo esc_html( $featured_label ); ?>
		</div>
	<?php endif; ?>

	<div class="flatwp-pricing-column__header">
		<?php if ( $plan_name ) : ?>
			<h3 class="flatwp-pricing-column__name"><?php echo esc_html( $plan_name ); ?></h3>
		<?php endif; ?>

		<?php if ( $plan_description ) : ?>
			<p class="flatwp-pricing-column__description"><?php echo esc_html( $plan_description ); ?></p>
		<?php endif; ?>
	</div>

	<div class="flatwp-pricing-column__price">
		<span class="flatwp-pricing-column__currency"><?php echo esc_html( $currency ); ?></span>
		<span class="flatwp-pricing-column__amount"><?php echo esc_html( $price ); ?></span>
		<?php if ( $period ) : ?>
			<span class="flatwp-pricing-column__period"><?php echo esc_html( $period ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $features ) ) : ?>
		<ul class="flatwp-pricing-column__features">
			<?php foreach ( $features as $feature ) : ?>
				<li class="flatwp-pricing-column__feature">
					<svg class="flatwp-pricing-column__check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $accent_color ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="20 6 9 17 4 12"/>
					</svg>
					<span><?php echo esc_html( $feature ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $button_text && $button_url ) : ?>
		<div class="flatwp-pricing-column__action">
			<a
				href="<?php echo esc_url( $button_url ); ?>"
				class="flatwp-button flatwp-button--<?php echo $featured ? 'solid' : 'outline'; ?>"
				style="<?php echo $featured ? 'background-color: ' . esc_attr( $accent_color ) . '; border-color: ' . esc_attr( $accent_color ) . ';' : 'color: ' . esc_attr( $accent_color ) . '; border-color: ' . esc_attr( $accent_color ) . ';'; ?>"
			>
				<?php echo esc_html( $button_text ); ?>
			</a>
		</div>
	<?php endif; ?>
</article>
