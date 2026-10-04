<?php
/**
 * Server-side rendering for the FAQ Item block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$question     = $attributes['question'] ?? 'Question goes here?';
$answer       = $attributes['answer'] ?? 'Answer goes here.';
$default_open = $attributes['defaultOpen'] ?? false;

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-faq-item',
);

if ( $default_open ) {
	$wrapper_classes[] = 'flatwp-faq-item--open';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);

// Generate unique ID for accessibility.
$unique_id = 'faq-' . wp_unique_id();
?>

<details <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $default_open ? 'open' : ''; ?>>
	<summary class="flatwp-faq-item__question" id="<?php echo esc_attr( $unique_id ); ?>-question">
		<span class="flatwp-faq-item__question-text"><?php echo esc_html( $question ); ?></span>
		<span class="flatwp-faq-item__icon" aria-hidden="true">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<polyline points="6 9 12 15 18 9"/>
			</svg>
		</span>
	</summary>
	<div class="flatwp-faq-item__answer" id="<?php echo esc_attr( $unique_id ); ?>-answer">
		<p><?php echo esc_html( $answer ); ?></p>
	</div>
</details>
