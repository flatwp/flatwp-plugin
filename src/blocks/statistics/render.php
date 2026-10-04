<?php
/**
 * Server-side rendering for the Statistics block.
 *
 * @package FlatWP
 * @since 0.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes with defaults.
$heading            = $attributes['heading'] ?? '';
$subheading         = $attributes['subheading'] ?? '';
$columns            = $attributes['columns'] ?? 4;
$background_color   = $attributes['backgroundColor'] ?? '#1f2937';
$text_color         = $attributes['textColor'] ?? '#ffffff';
$accent_color       = $attributes['accentColor'] ?? '#3b82f6';
$use_gradient       = $attributes['useGradient'] ?? false;
$gradient_from      = $attributes['gradientFrom'] ?? '#1f2937';
$gradient_to        = $attributes['gradientTo'] ?? '#111827';
$gradient_direction = $attributes['gradientDirection'] ?? 'to-br';
$padding_top        = $attributes['paddingTop'] ?? 'lg';
$padding_bottom     = $attributes['paddingBottom'] ?? 'lg';

// Get values from helpers.
$padding_top_value    = FlatWP_Blocks::get_padding_value( $padding_top );
$padding_bottom_value = FlatWP_Blocks::get_padding_value( $padding_bottom );
$gradient_dir         = FlatWP_Blocks::get_gradient_direction( $gradient_direction );

// Build wrapper classes.
$wrapper_classes = array(
	'flatwp-block',
	'flatwp-block--statistics',
);

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . esc_attr( $attributes['align'] );
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
$wrapper_styles[] = 'padding-top: ' . esc_attr( $padding_top_value ) . ';';
$wrapper_styles[] = 'padding-bottom: ' . esc_attr( $padding_bottom_value ) . ';';
$wrapper_styles[] = '--flatwp-stat-accent: ' . esc_attr( $accent_color ) . ';';

// Grid classes.
$grid_classes = array(
	'flatwp-statistics__grid',
	'flatwp-statistics__grid--' . esc_attr( $columns ) . '-cols',
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
		<?php if ( $heading || $subheading ) : ?>
			<div class="flatwp-statistics__header">
				<?php if ( $heading ) : ?>
					<h2 class="flatwp-statistics__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $subheading ) : ?>
					<p class="flatwp-statistics__subheading"><?php echo esc_html( $subheading ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
