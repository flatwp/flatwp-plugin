<?php
/**
 * FlatWP ACF Fields Manager
 *
 * Manages ACF Flexible Content blocks and page template system.
 * Provides enhanced UI with icons, validation, and user-friendly field configuration.
 *
 * @package FlatWP
 * @since 0.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_ACF_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Only initialize if ACF is active.
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			add_action( 'admin_notices', array( $this, 'acf_not_active_notice' ) );
			return;
		}

		// Register ACF field groups.
		add_action( 'acf/init', array( $this, 'register_flexible_content_fields' ) );
		add_action( 'acf/init', array( $this, 'register_sidebar_blocks_field' ) );
	}

	/**
	 * Show admin notice if ACF is not active.
	 */
	public function acf_not_active_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'FlatWP Companion:', 'flatwp-headless-connector' ); ?></strong>
				<?php
				printf(
					/* translators: %s: ACF plugin link */
					esc_html__( 'Advanced Custom Fields Pro is required for the block-based page builder. %s', 'flatwp-headless-connector' ),
					'<a href="https://www.advancedcustomfields.com/pro/" target="_blank">' . esc_html__( 'Get ACF Pro', 'flatwp-headless-connector' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Register ACF Flexible Content field groups.
	 */
	public function register_flexible_content_fields() {
		// Register the main Flexible Content field.
		acf_add_local_field_group( array(
			'key' => 'group_flatwp_flexible_content',
			'title' => __( 'Page Builder', 'flatwp-headless-connector' ),
			'fields' => array(
				array(
					'key' => 'field_flatwp_flexible_content',
					'label' => __( 'Content Blocks', 'flatwp-headless-connector' ),
					'name' => 'flexible_content',
					'type' => 'flexible_content',
					'instructions' => __( 'Add and arrange content blocks to build your page layout.', 'flatwp-headless-connector' ),
					'button_label' => __( 'Add Content Block', 'flatwp-headless-connector' ),
					'layouts' => $this->get_block_layouts(),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
		) );
	}

	/**
	 * Register ACF Sidebar Blocks field group.
	 */
	public function register_sidebar_blocks_field() {
		acf_add_local_field_group( array(
			'key' => 'group_flatwp_sidebar_blocks',
			'title' => __( 'Page Sidebar Content', 'flatwp-headless-connector' ),
			'fields' => array(
				array(
					'key' => 'field_flatwp_sidebar_blocks',
					'label' => __( 'Sidebar Blocks', 'flatwp-headless-connector' ),
					'name' => 'sidebar_blocks',
					'type' => 'flexible_content',
					'instructions' => __( 'Add content blocks for the page sidebar. These will only display if "Show Sidebar" is enabled in FlatWP Page Settings.', 'flatwp-headless-connector' ),
					'button_label' => __( 'Add Sidebar Block', 'flatwp-headless-connector' ),
					'layouts' => $this->get_sidebar_block_layouts(),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
				),
			),
			'menu_order' => 1,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
		) );
	}

	/**
	 * Get block layouts for Flexible Content.
	 *
	 * @return array Block layout configurations.
	 */
	private function get_block_layouts() {
		$layouts = array(
			// Hero Centered Block.
			'hero_centered' => array(
				'key' => 'layout_hero_centered',
				'name' => 'hero_centered',
				'label' => '🎯 ' . __( 'Hero - Centered', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_hero_centered_heading',
						'label' => __( 'Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'instructions' => __( 'Main headline (recommended: 40-60 characters)', 'flatwp-headless-connector' ),
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_hero_centered_subheading',
						'label' => __( 'Subheading', 'flatwp-headless-connector' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'instructions' => __( 'Supporting text (recommended: 100-150 characters)', 'flatwp-headless-connector' ),
						'rows' => 3,
						'maxlength' => 200,
					),
					array(
						'key' => 'field_hero_centered_cta_text',
						'label' => __( 'CTA Button Text', 'flatwp-headless-connector' ),
						'name' => 'cta_text',
						'type' => 'text',
						'instructions' => __( 'Button label (e.g., "Get Started", "Learn More")', 'flatwp-headless-connector' ),
						'maxlength' => 30,
					),
					array(
						'key' => 'field_hero_centered_cta_url',
						'label' => __( 'CTA Button URL', 'flatwp-headless-connector' ),
						'name' => 'cta_url',
						'type' => 'url',
						'instructions' => __( 'Where the button links to', 'flatwp-headless-connector' ),
					),
					array(
						'key' => 'field_hero_centered_background_image',
						'label' => __( 'Background Image (Optional)', 'flatwp-headless-connector' ),
						'name' => 'background_image',
						'type' => 'image',
						'instructions' => __( 'Recommended size: 1920x1080px', 'flatwp-headless-connector' ),
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
				),
			),

			// Hero Split Block.
			'hero_split' => array(
				'key' => 'layout_hero_split',
				'name' => 'hero_split',
				'label' => '📸 ' . __( 'Hero - Split with Image', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_hero_split_heading',
						'label' => __( 'Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_hero_split_subheading',
						'label' => __( 'Subheading', 'flatwp-headless-connector' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 3,
						'maxlength' => 200,
					),
					array(
						'key' => 'field_hero_split_image',
						'label' => __( 'Image', 'flatwp-headless-connector' ),
						'name' => 'image',
						'type' => 'image',
						'instructions' => __( 'Recommended size: 800x600px', 'flatwp-headless-connector' ),
						'required' => 1,
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
					array(
						'key' => 'field_hero_split_image_position',
						'label' => __( 'Image Position', 'flatwp-headless-connector' ),
						'name' => 'image_position',
						'type' => 'select',
						'choices' => array(
							'left' => __( 'Left', 'flatwp-headless-connector' ),
							'right' => __( 'Right', 'flatwp-headless-connector' ),
						),
						'default_value' => 'right',
					),
					array(
						'key' => 'field_hero_split_cta_text',
						'label' => __( 'CTA Button Text', 'flatwp-headless-connector' ),
						'name' => 'cta_text',
						'type' => 'text',
						'maxlength' => 30,
					),
					array(
						'key' => 'field_hero_split_cta_url',
						'label' => __( 'CTA Button URL', 'flatwp-headless-connector' ),
						'name' => 'cta_url',
						'type' => 'url',
					),
				),
			),

			// Features Grid Block.
			'features_grid' => array(
				'key' => 'layout_features_grid',
				'name' => 'features_grid',
				'label' => '⚡ ' . __( 'Features Grid', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_features_heading',
						'label' => __( 'Section Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_features_subheading',
						'label' => __( 'Section Subheading', 'flatwp-headless-connector' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 2,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_features_items',
						'label' => __( 'Features', 'flatwp-headless-connector' ),
						'name' => 'features',
						'type' => 'repeater',
						'instructions' => __( 'Add 3-6 features for best visual balance', 'flatwp-headless-connector' ),
						'layout' => 'block',
						'button_label' => __( 'Add Feature', 'flatwp-headless-connector' ),
						'sub_fields' => array(
							array(
								'key' => 'field_feature_icon',
								'label' => __( 'Icon (Emoji)', 'flatwp-headless-connector' ),
								'name' => 'icon',
								'type' => 'text',
								'instructions' => __( 'Single emoji character (e.g., ⚡ 🚀 💡)', 'flatwp-headless-connector' ),
								'maxlength' => 2,
							),
							array(
								'key' => 'field_feature_title',
								'label' => __( 'Title', 'flatwp-headless-connector' ),
								'name' => 'title',
								'type' => 'text',
								'required' => 1,
								'maxlength' => 50,
							),
							array(
								'key' => 'field_feature_description',
								'label' => __( 'Description', 'flatwp-headless-connector' ),
								'name' => 'description',
								'type' => 'textarea',
								'rows' => 2,
								'maxlength' => 150,
							),
						),
					),
				),
			),

			// Content Section Block.
			'content_section' => array(
				'key' => 'layout_content_section',
				'name' => 'content_section',
				'label' => '📝 ' . __( 'Content Section', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_content_heading',
						'label' => __( 'Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_content_text',
						'label' => __( 'Content', 'flatwp-headless-connector' ),
						'name' => 'content',
						'type' => 'wysiwyg',
						'instructions' => __( 'Rich text content with formatting', 'flatwp-headless-connector' ),
						'toolbar' => 'full',
						'media_upload' => 1,
					),
					array(
						'key' => 'field_content_image',
						'label' => __( 'Image (Optional)', 'flatwp-headless-connector' ),
						'name' => 'image',
						'type' => 'image',
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
					array(
						'key' => 'field_content_layout',
						'label' => __( 'Layout', 'flatwp-headless-connector' ),
						'name' => 'layout',
						'type' => 'select',
						'choices' => array(
							'single' => __( 'Full Width', 'flatwp-headless-connector' ),
							'two-column' => __( 'Two Columns', 'flatwp-headless-connector' ),
						),
						'default_value' => 'single',
					),
				),
			),

			// Pricing Block.
			'pricing' => array(
				'key' => 'layout_pricing',
				'name' => 'pricing',
				'label' => '💰 ' . __( 'Pricing Table', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_pricing_heading',
						'label' => __( 'Section Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_pricing_plans',
						'label' => __( 'Pricing Plans', 'flatwp-headless-connector' ),
						'name' => 'plans',
						'type' => 'repeater',
						'instructions' => __( 'Add 2-3 pricing tiers', 'flatwp-headless-connector' ),
						'layout' => 'block',
						'button_label' => __( 'Add Plan', 'flatwp-headless-connector' ),
						'sub_fields' => array(
							array(
								'key' => 'field_plan_name',
								'label' => __( 'Plan Name', 'flatwp-headless-connector' ),
								'name' => 'name',
								'type' => 'text',
								'required' => 1,
								'maxlength' => 30,
							),
							array(
								'key' => 'field_plan_price',
								'label' => __( 'Price', 'flatwp-headless-connector' ),
								'name' => 'price',
								'type' => 'text',
								'instructions' => __( 'e.g., $29/month', 'flatwp-headless-connector' ),
								'required' => 1,
								'maxlength' => 20,
							),
							array(
								'key' => 'field_plan_features',
								'label' => __( 'Features', 'flatwp-headless-connector' ),
								'name' => 'features',
								'type' => 'textarea',
								'instructions' => __( 'One feature per line', 'flatwp-headless-connector' ),
								'rows' => 5,
							),
							array(
								'key' => 'field_plan_cta_text',
								'label' => __( 'Button Text', 'flatwp-headless-connector' ),
								'name' => 'cta_text',
								'type' => 'text',
								'default_value' => __( 'Get Started', 'flatwp-headless-connector' ),
								'maxlength' => 30,
							),
							array(
								'key' => 'field_plan_cta_url',
								'label' => __( 'Button URL', 'flatwp-headless-connector' ),
								'name' => 'cta_url',
								'type' => 'url',
							),
							array(
								'key' => 'field_plan_featured',
								'label' => __( 'Featured Plan', 'flatwp-headless-connector' ),
								'name' => 'featured',
								'type' => 'true_false',
								'instructions' => __( 'Highlight this plan', 'flatwp-headless-connector' ),
								'default_value' => 0,
							),
						),
					),
				),
			),

			// Testimonials Block.
			'testimonials' => array(
				'key' => 'layout_testimonials',
				'name' => 'testimonials',
				'label' => '💬 ' . __( 'Testimonials', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_testimonials_heading',
						'label' => __( 'Section Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_testimonials_items',
						'label' => __( 'Testimonials', 'flatwp-headless-connector' ),
						'name' => 'testimonials',
						'type' => 'repeater',
						'instructions' => __( 'Add 3-6 testimonials', 'flatwp-headless-connector' ),
						'layout' => 'block',
						'button_label' => __( 'Add Testimonial', 'flatwp-headless-connector' ),
						'sub_fields' => array(
							array(
								'key' => 'field_testimonial_quote',
								'label' => __( 'Quote', 'flatwp-headless-connector' ),
								'name' => 'quote',
								'type' => 'textarea',
								'required' => 1,
								'rows' => 3,
								'maxlength' => 300,
							),
							array(
								'key' => 'field_testimonial_author',
								'label' => __( 'Author Name', 'flatwp-headless-connector' ),
								'name' => 'author',
								'type' => 'text',
								'required' => 1,
								'maxlength' => 50,
							),
							array(
								'key' => 'field_testimonial_role',
								'label' => __( 'Role/Company', 'flatwp-headless-connector' ),
								'name' => 'role',
								'type' => 'text',
								'maxlength' => 100,
							),
							array(
								'key' => 'field_testimonial_avatar',
								'label' => __( 'Avatar (Optional)', 'flatwp-headless-connector' ),
								'name' => 'avatar',
								'type' => 'image',
								'return_format' => 'array',
								'preview_size' => 'thumbnail',
							),
						),
					),
				),
			),

			// CTA Simple Block.
			'cta_simple' => array(
				'key' => 'layout_cta_simple',
				'name' => 'cta_simple',
				'label' => '📣 ' . __( 'Call-to-Action - Simple', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_cta_simple_heading',
						'label' => __( 'Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_cta_simple_subheading',
						'label' => __( 'Subheading', 'flatwp-headless-connector' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 2,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_cta_simple_button_text',
						'label' => __( 'Button Text', 'flatwp-headless-connector' ),
						'name' => 'button_text',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 30,
					),
					array(
						'key' => 'field_cta_simple_button_url',
						'label' => __( 'Button URL', 'flatwp-headless-connector' ),
						'name' => 'button_url',
						'type' => 'url',
						'required' => 1,
					),
				),
			),

			// CTA Boxed Block.
			'cta_boxed' => array(
				'key' => 'layout_cta_boxed',
				'name' => 'cta_boxed',
				'label' => '🎁 ' . __( 'Call-to-Action - Boxed', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_cta_boxed_heading',
						'label' => __( 'Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_cta_boxed_subheading',
						'label' => __( 'Subheading', 'flatwp-headless-connector' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 2,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_cta_boxed_button_text',
						'label' => __( 'Button Text', 'flatwp-headless-connector' ),
						'name' => 'button_text',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 30,
					),
					array(
						'key' => 'field_cta_boxed_button_url',
						'label' => __( 'Button URL', 'flatwp-headless-connector' ),
						'name' => 'button_url',
						'type' => 'url',
						'required' => 1,
					),
					array(
						'key' => 'field_cta_boxed_background_color',
						'label' => __( 'Background Style', 'flatwp-headless-connector' ),
						'name' => 'background_color',
						'type' => 'select',
						'choices' => array(
							'gradient-blue' => __( 'Blue Gradient', 'flatwp-headless-connector' ),
							'gradient-purple' => __( 'Purple Gradient', 'flatwp-headless-connector' ),
							'gradient-green' => __( 'Green Gradient', 'flatwp-headless-connector' ),
							'solid-dark' => __( 'Solid Dark', 'flatwp-headless-connector' ),
						),
						'default_value' => 'gradient-blue',
					),
				),
			),
		);

		// Load and merge core blocks if available
		if ( file_exists( FLATWP_PLUGIN_DIR . 'includes/blocks/class-flatwp-core-blocks.php' ) ) {
			require_once FLATWP_PLUGIN_DIR . 'includes/blocks/class-flatwp-core-blocks.php';
			$core_blocks = FlatWP_Core_Blocks::get_blocks();
			$layouts = array_merge( $layouts, $core_blocks );
		}

		return apply_filters( 'flatwp_block_layouts', $layouts );
	}

	/**
	 * Get sidebar block layouts for Flexible Content.
	 *
	 * @return array Sidebar block layout configurations.
	 */
	private function get_sidebar_block_layouts() {
		$layouts = array(
			// Content Widget Block.
			'content_section' => array(
				'key' => 'layout_sidebar_content_section',
				'name' => 'content_section',
				'label' => '📝 ' . __( 'Content Widget', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_sidebar_content_title',
						'label' => __( 'Title', 'flatwp-headless-connector' ),
						'name' => 'title',
						'type' => 'text',
						'instructions' => __( 'Widget title (optional)', 'flatwp-headless-connector' ),
						'required' => 0,
						'maxlength' => 60,
					),
					array(
						'key' => 'field_sidebar_content_content',
						'label' => __( 'Content', 'flatwp-headless-connector' ),
						'name' => 'content',
						'type' => 'wysiwyg',
						'instructions' => __( 'Widget content with text, images, and links', 'flatwp-headless-connector' ),
						'required' => 1,
						'tabs' => 'all',
						'toolbar' => 'basic',
						'media_upload' => 1,
					),
				),
			),

			// CTA Widget Block.
			'cta_simple' => array(
				'key' => 'layout_sidebar_cta_simple',
				'name' => 'cta_simple',
				'label' => '📣 ' . __( 'CTA Widget', 'flatwp-headless-connector' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_sidebar_cta_heading',
						'label' => __( 'Heading', 'flatwp-headless-connector' ),
						'name' => 'heading',
						'type' => 'text',
						'instructions' => __( 'Call to action heading', 'flatwp-headless-connector' ),
						'required' => 1,
						'maxlength' => 50,
					),
					array(
						'key' => 'field_sidebar_cta_text',
						'label' => __( 'Text', 'flatwp-headless-connector' ),
						'name' => 'text',
						'type' => 'textarea',
						'instructions' => __( 'Supporting text for the CTA', 'flatwp-headless-connector' ),
						'required' => 0,
						'rows' => 3,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_sidebar_cta_button_text',
						'label' => __( 'Button Text', 'flatwp-headless-connector' ),
						'name' => 'button_text',
						'type' => 'text',
						'instructions' => __( 'Button label', 'flatwp-headless-connector' ),
						'required' => 1,
						'default_value' => __( 'Learn More', 'flatwp-headless-connector' ),
						'maxlength' => 30,
					),
					array(
						'key' => 'field_sidebar_cta_button_url',
						'label' => __( 'Button URL', 'flatwp-headless-connector' ),
						'name' => 'button_url',
						'type' => 'url',
						'instructions' => __( 'Where the button links to', 'flatwp-headless-connector' ),
						'required' => 1,
					),
				),
			),
		);

		return apply_filters( 'flatwp_sidebar_block_layouts', $layouts );
	}
}
