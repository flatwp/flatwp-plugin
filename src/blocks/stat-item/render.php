<?php
/**
 * Server-side rendering for the Stat Item block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$value  = $attributes['value'] ?? '100+';
$label  = $attributes['label'] ?? 'Stat Label';
$prefix = $attributes['prefix'] ?? '';
$suffix = $attributes['suffix'] ?? '';

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-stat-item',
);

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="flatwp-stat-item__value">
		<?php if ( $prefix ) : ?>
			<span class="flatwp-stat-item__prefix"><?php echo esc_html( $prefix ); ?></span>
		<?php endif; ?>
		<span class="flatwp-stat-item__number"><?php echo esc_html( $value ); ?></span>
		<?php if ( $suffix ) : ?>
			<span class="flatwp-stat-item__suffix"><?php echo esc_html( $suffix ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( $label ) : ?>
		<p class="flatwp-stat-item__label"><?php echo esc_html( $label ); ?></p>
	<?php endif; ?>
</div>
