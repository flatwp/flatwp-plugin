<?php
/**
 * Pricing Column Block - Server-side Render
 *
 * This file renders the block on the frontend and exposes
 * structured data for GraphQL consumption.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 *
 * @package FlatWP
 */

// Extract attributes with defaults.
$title           = $attributes['title'] ?? 'Basic';
$price           = $attributes['price'] ?? '$29';
$period          = $attributes['period'] ?? '/month';
$description     = $attributes['description'] ?? '';
$features        = $attributes['features'] ?? array();
$cta_text        = $attributes['ctaText'] ?? 'Get Started';
$cta_url         = $attributes['ctaUrl'] ?? '#';
$highlighted     = $attributes['highlighted'] ?? false;
$highlight_label = $attributes['highlightLabel'] ?? 'Most Popular';

// Build class names.
$wrapper_classes = array( 'flatwp-pricing-column' );
if ( $highlighted ) {
	$wrapper_classes[] = 'is-highlighted';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);

// SVG icons for features.
$check_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"/></svg>';
$close_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"/></svg>';
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $highlighted && $highlight_label ) : ?>
		<div class="flatwp-pricing-badge">
			<?php echo esc_html( $highlight_label ); ?>
		</div>
	<?php endif; ?>

	<div class="flatwp-pricing-header">
		<h3 class="flatwp-pricing-title"><?php echo esc_html( $title ); ?></h3>
		<div class="flatwp-pricing-price">
			<span class="flatwp-pricing-amount"><?php echo esc_html( $price ); ?></span>
			<span class="flatwp-pricing-period"><?php echo esc_html( $period ); ?></span>
		</div>
		<?php if ( $description ) : ?>
			<p class="flatwp-pricing-description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $features ) ) : ?>
		<ul class="flatwp-pricing-features">
			<?php foreach ( $features as $feature ) : ?>
				<?php
				$feature_text     = $feature['text'] ?? '';
				$feature_included = $feature['included'] ?? true;
				$feature_class    = $feature_included ? 'is-included' : 'is-excluded';
				$feature_icon     = $feature_included ? $check_icon : $close_icon;
				?>
				<li class="flatwp-feature <?php echo esc_attr( $feature_class ); ?>">
					<?php echo $feature_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $feature_text ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div class="flatwp-pricing-cta">
		<a href="<?php echo esc_url( $cta_url ); ?>" class="flatwp-pricing-button">
			<?php echo esc_html( $cta_text ); ?>
		</a>
	</div>
</div>
