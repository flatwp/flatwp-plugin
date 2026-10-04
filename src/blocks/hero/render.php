<?php
/**
 * Server-side rendering for the Hero block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$variant              = $attributes['variant'] ?? 'centered';
$heading              = $attributes['heading'] ?? '';
$subheading           = $attributes['subheading'] ?? '';
$alignment            = $attributes['alignment'] ?? 'center';
$primary_btn_text     = $attributes['primaryButtonText'] ?? '';
$primary_btn_url      = $attributes['primaryButtonUrl'] ?? '#';
$primary_btn_style    = $attributes['primaryButtonStyle'] ?? 'solid';
$secondary_btn_text   = $attributes['secondaryButtonText'] ?? '';
$secondary_btn_url    = $attributes['secondaryButtonUrl'] ?? '';
$background_image_url = $attributes['backgroundImageUrl'] ?? '';
$overlay_opacity      = $attributes['overlayOpacity'] ?? 50;
$overlay_color        = $attributes['overlayColor'] ?? '#000000';
$gradient_from        = $attributes['gradientFrom'] ?? '#2563eb';
$gradient_to          = $attributes['gradientTo'] ?? '#7c3aed';
$gradient_direction   = $attributes['gradientDirection'] ?? 'to-r';
$min_height           = $attributes['minHeight'] ?? '80vh';
$color_scheme         = $attributes['colorScheme'] ?? 'light';
$side_image_url       = $attributes['sideImageUrl'] ?? '';
$image_position       = $attributes['imagePosition'] ?? 'right';

// Get gradient direction CSS.
$gradient_directions = array(
	'to-r'  => 'to right',
	'to-l'  => 'to left',
	'to-b'  => 'to bottom',
	'to-t'  => 'to top',
	'to-br' => 'to bottom right',
	'to-bl' => 'to bottom left',
);
$gradient_dir = isset( $gradient_directions[ $gradient_direction ] ) ? $gradient_directions[ $gradient_direction ] : 'to right';

// Color scheme styles.
$scheme_styles = array(
	'light'   => array( 'bg' => '#ffffff', 'text' => '#1f2937' ),
	'dark'    => array( 'bg' => '#111827', 'text' => '#f9fafb' ),
	'primary' => array( 'bg' => '#3b82f6', 'text' => '#ffffff' ),
);
$scheme = isset( $scheme_styles[ $color_scheme ] ) ? $scheme_styles[ $color_scheme ] : $scheme_styles['light'];

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--hero',
	'flatwp-hero--' . esc_attr( $variant ),
	'flatwp-hero--' . esc_attr( $color_scheme ),
	'align-' . esc_attr( $alignment ),
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
}

// Build inline styles.
$wrapper_styles = array(
	'min-height: ' . esc_attr( $min_height ) . ';',
	'--overlay-opacity: ' . esc_attr( $overlay_opacity / 100 ) . ';',
	'--overlay-color: ' . esc_attr( $overlay_color ) . ';',
	'--gradient-from: ' . esc_attr( $gradient_from ) . ';',
	'--gradient-to: ' . esc_attr( $gradient_to ) . ';',
);

if ( 'gradient' === $variant ) {
	$wrapper_styles[] = sprintf(
		'background: linear-gradient(%s, %s, %s);',
		esc_attr( $gradient_dir ),
		esc_attr( $gradient_from ),
		esc_attr( $gradient_to )
	);
} elseif ( 'image-bg' !== $variant && 'custom' !== $color_scheme ) {
	$wrapper_styles[] = 'background-color: ' . esc_attr( $scheme['bg'] ) . ';';
	$wrapper_styles[] = 'color: ' . esc_attr( $scheme['text'] ) . ';';
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);

// Button classes.
$primary_btn_classes = 'flatwp-hero__button flatwp-hero__button--' . esc_attr( $primary_btn_style );
$secondary_btn_classes = 'flatwp-hero__button flatwp-hero__button--outline';
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( 'image-bg' === $variant && $background_image_url ) : ?>
		<div class="flatwp-hero__bg-image" style="background-image: url(<?php echo esc_url( $background_image_url ); ?>);"></div>
		<div class="flatwp-hero__overlay"></div>
	<?php endif; ?>

	<?php if ( 'gradient' === $variant ) : ?>
		<div class="flatwp-hero__gradient"></div>
	<?php endif; ?>

	<div class="flatwp-hero__container<?php echo 'split' === $variant ? ' flatwp-hero__container--split image-' . esc_attr( $image_position ) : ''; ?>">
		<div class="flatwp-hero__content">
			<?php if ( $heading ) : ?>
				<h1 class="flatwp-hero__heading"><?php echo wp_kses_post( $heading ); ?></h1>
			<?php endif; ?>

			<?php if ( $subheading ) : ?>
				<p class="flatwp-hero__subheading"><?php echo wp_kses_post( $subheading ); ?></p>
			<?php endif; ?>

			<?php if ( $primary_btn_text || $secondary_btn_text ) : ?>
				<div class="flatwp-hero__buttons">
					<?php if ( $primary_btn_text ) : ?>
						<a href="<?php echo esc_url( $primary_btn_url ); ?>" class="<?php echo esc_attr( $primary_btn_classes ); ?>">
							<?php echo esc_html( $primary_btn_text ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $secondary_btn_text ) : ?>
						<a href="<?php echo esc_url( $secondary_btn_url ); ?>" class="<?php echo esc_attr( $secondary_btn_classes ); ?>">
							<?php echo esc_html( $secondary_btn_text ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( 'split' === $variant ) : ?>
			<div class="flatwp-hero__image">
				<?php if ( $side_image_url ) : ?>
					<img src="<?php echo esc_url( $side_image_url ); ?>" alt="" />
				<?php else : ?>
					<div class="flatwp-hero__image-placeholder">
						<span><?php esc_html_e( 'Add image in editor', 'flatwp' ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
