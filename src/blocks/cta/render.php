<?php
/**
 * Server-side rendering for the CTA block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$variant            = $attributes['variant'] ?? 'boxed';
$heading            = $attributes['heading'] ?? '';
$text               = $attributes['text'] ?? '';
$button_text        = $attributes['buttonText'] ?? 'Get Started';
$button_url         = $attributes['buttonUrl'] ?? '';
$button_style       = $attributes['buttonStyle'] ?? 'solid';
$secondary_btn_text = $attributes['secondaryButtonText'] ?? '';
$secondary_btn_url  = $attributes['secondaryButtonUrl'] ?? '';
$color_scheme       = $attributes['colorScheme'] ?? 'primary';
$alignment          = $attributes['alignment'] ?? 'center';
$gradient_from      = $attributes['gradientFrom'] ?? '#2563eb';
$gradient_to        = $attributes['gradientTo'] ?? '#7c3aed';
$image_url          = $attributes['imageUrl'] ?? '';

// Color scheme styles.
$scheme_styles = array(
	'light'    => array( 'bg' => '#ffffff', 'text' => '#1f2937' ),
	'dark'     => array( 'bg' => '#111827', 'text' => '#f9fafb' ),
	'primary'  => array( 'bg' => '#3b82f6', 'text' => '#ffffff' ),
	'gradient' => array( 'bg' => 'gradient', 'text' => '#ffffff' ),
);
$scheme = isset( $scheme_styles[ $color_scheme ] ) ? $scheme_styles[ $color_scheme ] : $scheme_styles['primary'];

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--cta',
	'flatwp-cta--' . esc_attr( $variant ),
	'flatwp-cta--' . esc_attr( $color_scheme ),
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

// Build inline styles.
$wrapper_styles = array();

if ( 'gradient' === $color_scheme ) {
	$wrapper_styles[] = sprintf(
		'background: linear-gradient(to right, %s, %s);',
		esc_attr( $gradient_from ),
		esc_attr( $gradient_to )
	);
} else {
	$wrapper_styles[] = 'background-color: ' . esc_attr( $scheme['bg'] ) . ';';
}

$wrapper_styles[] = 'color: ' . esc_attr( $scheme['text'] ) . ';';

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);

// Button classes and styles.
$primary_btn_classes = 'flatwp-cta__button flatwp-cta__button--' . esc_attr( $button_style );
$secondary_btn_classes = 'flatwp-cta__button flatwp-cta__button--outline';
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="flatwp-container">
		<?php if ( 'split' === $variant && $image_url ) : ?>
			<div class="flatwp-cta__grid">
				<div class="flatwp-cta__content align-<?php echo esc_attr( $alignment ); ?>">
					<?php if ( $heading ) : ?>
						<h2 class="flatwp-cta__heading"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>

					<?php if ( $text ) : ?>
						<p class="flatwp-cta__text"><?php echo wp_kses_post( $text ); ?></p>
					<?php endif; ?>

					<?php if ( $button_text || $secondary_btn_text ) : ?>
						<div class="flatwp-cta__buttons">
							<?php if ( $button_text && $button_url ) : ?>
								<a href="<?php echo esc_url( $button_url ); ?>" class="<?php echo esc_attr( $primary_btn_classes ); ?>">
									<?php echo esc_html( $button_text ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $secondary_btn_text && $secondary_btn_url ) : ?>
								<a href="<?php echo esc_url( $secondary_btn_url ); ?>" class="<?php echo esc_attr( $secondary_btn_classes ); ?>">
									<?php echo esc_html( $secondary_btn_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="flatwp-cta__image">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
				</div>
			</div>
		<?php elseif ( 'inline' === $variant ) : ?>
			<div class="flatwp-cta__inline">
				<div class="flatwp-cta__content align-<?php echo esc_attr( $alignment ); ?>">
					<?php if ( $heading ) : ?>
						<h2 class="flatwp-cta__heading"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>

					<?php if ( $text ) : ?>
						<p class="flatwp-cta__text"><?php echo wp_kses_post( $text ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $button_text || $secondary_btn_text ) : ?>
					<div class="flatwp-cta__buttons">
						<?php if ( $button_text && $button_url ) : ?>
							<a href="<?php echo esc_url( $button_url ); ?>" class="<?php echo esc_attr( $primary_btn_classes ); ?>">
								<?php echo esc_html( $button_text ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $secondary_btn_text && $secondary_btn_url ) : ?>
							<a href="<?php echo esc_url( $secondary_btn_url ); ?>" class="<?php echo esc_attr( $secondary_btn_classes ); ?>">
								<?php echo esc_html( $secondary_btn_text ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="flatwp-cta__content align-<?php echo esc_attr( $alignment ); ?>">
				<?php if ( $heading ) : ?>
					<h2 class="flatwp-cta__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $text ) : ?>
					<p class="flatwp-cta__text"><?php echo wp_kses_post( $text ); ?></p>
				<?php endif; ?>

				<?php if ( $button_text || $secondary_btn_text ) : ?>
					<div class="flatwp-cta__buttons">
						<?php if ( $button_text && $button_url ) : ?>
							<a href="<?php echo esc_url( $button_url ); ?>" class="<?php echo esc_attr( $primary_btn_classes ); ?>">
								<?php echo esc_html( $button_text ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $secondary_btn_text && $secondary_btn_url ) : ?>
							<a href="<?php echo esc_url( $secondary_btn_url ); ?>" class="<?php echo esc_attr( $secondary_btn_classes ); ?>">
								<?php echo esc_html( $secondary_btn_text ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
