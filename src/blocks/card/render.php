<?php
/**
 * Server-side rendering for the Card block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$title              = $attributes['title'] ?? 'Card Title';
$description        = $attributes['description'] ?? '';
$image_url          = $attributes['imageUrl'] ?? '';
$image_alt          = $attributes['imageAlt'] ?? '';
$link_url           = $attributes['linkUrl'] ?? '';
$link_text          = $attributes['linkText'] ?? '';
$background_color   = $attributes['backgroundColor'] ?? '#ffffff';
$text_color         = $attributes['textColor'] ?? '#1f2937';
$use_gradient       = $attributes['useGradient'] ?? false;
$gradient_from      = $attributes['gradientFrom'] ?? '#ffffff';
$gradient_to        = $attributes['gradientTo'] ?? '#f3f4f6';
$gradient_direction = $attributes['gradientDirection'] ?? 'to-b';
$border_color       = $attributes['borderColor'] ?? '#e5e7eb';
$border_width       = $attributes['borderWidth'] ?? 1;
$border_radius      = $attributes['borderRadius'] ?? 'lg';
$shadow             = $attributes['shadow'] ?? 'md';
$hover_effect       = $attributes['hoverEffect'] ?? 'lift';
$padding            = $attributes['padding'] ?? 'md';
$accent_color       = $attributes['accentColor'] ?? '#3b82f6';

// Get values from helpers.
$border_radius_value = FlatWP_Blocks::get_border_radius( $border_radius );
$shadow_value        = FlatWP_Blocks::get_shadow( $shadow );
$gradient_dir        = FlatWP_Blocks::get_gradient_direction( $gradient_direction );

// Padding values.
$padding_values = array(
	'sm' => '1rem',
	'md' => '1.5rem',
	'lg' => '2rem',
);
$padding_value = $padding_values[ $padding ] ?? '1.5rem';

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--card',
);

if ( 'none' !== $hover_effect ) {
	$wrapper_classes[] = 'flatwp-card--hover-' . esc_attr( $hover_effect );
}

if ( $image_url ) {
	$wrapper_classes[] = 'flatwp-card--has-image';
}

// Build inline styles.
$wrapper_styles = array();

if ( $use_gradient ) {
	$wrapper_styles[] = sprintf(
		'background: linear-gradient(%s, %s, %s);',
		esc_attr( $gradient_dir ),
		esc_attr( $gradient_from ),
		esc_attr( $gradient_to )
	);
} else {
	$wrapper_styles[] = 'background-color: ' . esc_attr( $background_color ) . ';';
}

$wrapper_styles[] = 'color: ' . esc_attr( $text_color ) . ';';
$wrapper_styles[] = 'border: ' . esc_attr( $border_width ) . 'px solid ' . esc_attr( $border_color ) . ';';
$wrapper_styles[] = 'border-radius: ' . esc_attr( $border_radius_value ) . ';';
$wrapper_styles[] = 'box-shadow: ' . esc_attr( $shadow_value ) . ';';
$wrapper_styles[] = '--flatwp-card-accent: ' . esc_attr( $accent_color ) . ';';

// Content padding.
$content_styles = array( 'padding: ' . esc_attr( $padding_value ) . ';' );

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => implode( ' ', $wrapper_styles ),
	)
);
?>

<article <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $image_url ) : ?>
		<div class="flatwp-card__image">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
		</div>
	<?php endif; ?>

	<div class="flatwp-card__content" style="<?php echo esc_attr( implode( ' ', $content_styles ) ); ?>">
		<?php if ( $title ) : ?>
			<h3 class="flatwp-card__title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( $description ) : ?>
			<p class="flatwp-card__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<?php if ( $link_url && $link_text ) : ?>
			<a href="<?php echo esc_url( $link_url ); ?>" class="flatwp-card__link">
				<?php echo esc_html( $link_text ); ?>
				<svg class="flatwp-card__link-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</a>
		<?php endif; ?>
	</div>
</article>
