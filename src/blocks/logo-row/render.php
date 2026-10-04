<?php
/**
 * Server-side rendering for the Logo Row block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$heading            = $attributes['heading'] ?? 'Trusted by Industry Leaders';
$logos              = $attributes['logos'] ?? array();
$background_color   = $attributes['backgroundColor'] ?? '#f9fafb';
$logo_opacity       = $attributes['logoOpacity'] ?? 60;
$logo_hover_opacity = $attributes['logoHoverOpacity'] ?? 100;
$logo_max_height    = $attributes['logoMaxHeight'] ?? 48;
$grayscale          = $attributes['grayscale'] ?? true;
$padding_top        = $attributes['paddingTop'] ?? 'md';
$padding_bottom     = $attributes['paddingBottom'] ?? 'md';

// Get values from helpers.
$padding_top_value    = FlatWP_Blocks::get_padding_value( $padding_top );
$padding_bottom_value = FlatWP_Blocks::get_padding_value( $padding_bottom );

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--logo-row',
);

if ( $grayscale ) {
	$wrapper_classes[] = 'flatwp-logo-row--grayscale';
}

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

// Build inline styles.
$wrapper_styles = array(
	'background-color: ' . esc_attr( $background_color ) . ';',
	'padding-top: ' . esc_attr( $padding_top_value ) . ';',
	'padding-bottom: ' . esc_attr( $padding_bottom_value ) . ';',
	'--flatwp-logo-opacity: ' . esc_attr( $logo_opacity / 100 ) . ';',
	'--flatwp-logo-hover-opacity: ' . esc_attr( $logo_hover_opacity / 100 ) . ';',
	'--flatwp-logo-max-height: ' . esc_attr( $logo_max_height ) . 'px;',
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
		<?php if ( $heading ) : ?>
			<p class="flatwp-logo-row__heading"><?php echo esc_html( $heading ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $logos ) ) : ?>
			<div class="flatwp-logo-row__logos">
				<?php foreach ( $logos as $logo ) : ?>
					<?php if ( ! empty( $logo['url'] ) ) : ?>
						<div class="flatwp-logo-row__logo">
							<?php if ( ! empty( $logo['link'] ) ) : ?>
								<a href="<?php echo esc_url( $logo['link'] ); ?>" target="_blank" rel="noopener noreferrer">
									<img
										src="<?php echo esc_url( $logo['url'] ); ?>"
										alt="<?php echo esc_attr( $logo['alt'] ?? '' ); ?>"
									/>
								</a>
							<?php else : ?>
								<img
									src="<?php echo esc_url( $logo['url'] ); ?>"
									alt="<?php echo esc_attr( $logo['alt'] ?? '' ); ?>"
								/>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="flatwp-logo-row__empty"><?php esc_html_e( 'Add logos in the block settings.', 'flatwp-headless-connector' ); ?></p>
		<?php endif; ?>
	</div>
</section>
