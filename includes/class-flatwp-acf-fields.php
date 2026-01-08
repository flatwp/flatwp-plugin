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
	 * Post meta key for page template.
	 */
	const TEMPLATE_META_KEY = '_flatwp_page_template';

	/**
	 * Available page templates with their block configurations.
	 *
	 * @var array
	 */
	private $templates = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->define_templates();
		$this->init_hooks();
	}

	/**
	 * Define available page templates.
	 */
	private function define_templates() {
		$this->templates = array(
			'homepage' => array(
				'label'       => __( 'Homepage Template', 'flatwp' ),
				'description' => __( 'Hero section, features grid, testimonials, and call-to-action', 'flatwp' ),
				'icon'        => '🏠',
				'blocks'      => array( 'hero_centered', 'features_grid', 'testimonials', 'cta_boxed' ),
			),
			'landing' => array(
				'label'       => __( 'Landing Page Template', 'flatwp' ),
				'description' => __( 'Split hero, content section, pricing, and call-to-action', 'flatwp' ),
				'icon'        => '🚀',
				'blocks'      => array( 'hero_split', 'content_section', 'pricing', 'cta_simple' ),
			),
		);

		// Allow themes/plugins to add more templates.
		$this->templates = apply_filters( 'flatwp_page_templates', $this->templates );
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

		// Add template selection meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_template_meta_box' ) );
		add_action( 'save_post_page', array( $this, 'save_template_meta_box' ), 10, 2 );

		// AJAX handler for template application.
		add_action( 'wp_ajax_flatwp_apply_template', array( $this, 'ajax_apply_template' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
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
				<strong><?php esc_html_e( 'FlatWP Companion:', 'flatwp' ); ?></strong>
				<?php
				printf(
					/* translators: %s: ACF plugin link */
					esc_html__( 'Advanced Custom Fields Pro is required for the block-based page builder. %s', 'flatwp' ),
					'<a href="https://www.advancedcustomfields.com/pro/" target="_blank">' . esc_html__( 'Get ACF Pro', 'flatwp' ) . '</a>'
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
			'title' => __( 'Page Builder', 'flatwp' ),
			'fields' => array(
				array(
					'key' => 'field_flatwp_flexible_content',
					'label' => __( 'Content Blocks', 'flatwp' ),
					'name' => 'flexible_content',
					'type' => 'flexible_content',
					'instructions' => __( 'Add and arrange content blocks to build your page layout.', 'flatwp' ),
					'button_label' => __( 'Add Content Block', 'flatwp' ),
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
			'title' => __( 'Page Sidebar Content', 'flatwp' ),
			'fields' => array(
				array(
					'key' => 'field_flatwp_sidebar_blocks',
					'label' => __( 'Sidebar Blocks', 'flatwp' ),
					'name' => 'sidebar_blocks',
					'type' => 'flexible_content',
					'instructions' => __( 'Add content blocks for the page sidebar. These will only display if "Show Sidebar" is enabled in FlatWP Page Settings.', 'flatwp' ),
					'button_label' => __( 'Add Sidebar Block', 'flatwp' ),
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
				'label' => '🎯 ' . __( 'Hero - Centered', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_hero_centered_heading',
						'label' => __( 'Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'instructions' => __( 'Main headline (recommended: 40-60 characters)', 'flatwp' ),
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_hero_centered_subheading',
						'label' => __( 'Subheading', 'flatwp' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'instructions' => __( 'Supporting text (recommended: 100-150 characters)', 'flatwp' ),
						'rows' => 3,
						'maxlength' => 200,
					),
					array(
						'key' => 'field_hero_centered_cta_text',
						'label' => __( 'CTA Button Text', 'flatwp' ),
						'name' => 'cta_text',
						'type' => 'text',
						'instructions' => __( 'Button label (e.g., "Get Started", "Learn More")', 'flatwp' ),
						'maxlength' => 30,
					),
					array(
						'key' => 'field_hero_centered_cta_url',
						'label' => __( 'CTA Button URL', 'flatwp' ),
						'name' => 'cta_url',
						'type' => 'url',
						'instructions' => __( 'Where the button links to', 'flatwp' ),
					),
					array(
						'key' => 'field_hero_centered_background_image',
						'label' => __( 'Background Image (Optional)', 'flatwp' ),
						'name' => 'background_image',
						'type' => 'image',
						'instructions' => __( 'Recommended size: 1920x1080px', 'flatwp' ),
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
				),
			),

			// Hero Split Block.
			'hero_split' => array(
				'key' => 'layout_hero_split',
				'name' => 'hero_split',
				'label' => '📸 ' . __( 'Hero - Split with Image', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_hero_split_heading',
						'label' => __( 'Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_hero_split_subheading',
						'label' => __( 'Subheading', 'flatwp' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 3,
						'maxlength' => 200,
					),
					array(
						'key' => 'field_hero_split_image',
						'label' => __( 'Image', 'flatwp' ),
						'name' => 'image',
						'type' => 'image',
						'instructions' => __( 'Recommended size: 800x600px', 'flatwp' ),
						'required' => 1,
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
					array(
						'key' => 'field_hero_split_image_position',
						'label' => __( 'Image Position', 'flatwp' ),
						'name' => 'image_position',
						'type' => 'select',
						'choices' => array(
							'left' => __( 'Left', 'flatwp' ),
							'right' => __( 'Right', 'flatwp' ),
						),
						'default_value' => 'right',
					),
					array(
						'key' => 'field_hero_split_cta_text',
						'label' => __( 'CTA Button Text', 'flatwp' ),
						'name' => 'cta_text',
						'type' => 'text',
						'maxlength' => 30,
					),
					array(
						'key' => 'field_hero_split_cta_url',
						'label' => __( 'CTA Button URL', 'flatwp' ),
						'name' => 'cta_url',
						'type' => 'url',
					),
				),
			),

			// Features Grid Block.
			'features_grid' => array(
				'key' => 'layout_features_grid',
				'name' => 'features_grid',
				'label' => '⚡ ' . __( 'Features Grid', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_features_heading',
						'label' => __( 'Section Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_features_subheading',
						'label' => __( 'Section Subheading', 'flatwp' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 2,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_features_items',
						'label' => __( 'Features', 'flatwp' ),
						'name' => 'features',
						'type' => 'repeater',
						'instructions' => __( 'Add 3-6 features for best visual balance', 'flatwp' ),
						'layout' => 'block',
						'button_label' => __( 'Add Feature', 'flatwp' ),
						'sub_fields' => array(
							array(
								'key' => 'field_feature_icon',
								'label' => __( 'Icon (Emoji)', 'flatwp' ),
								'name' => 'icon',
								'type' => 'text',
								'instructions' => __( 'Single emoji character (e.g., ⚡ 🚀 💡)', 'flatwp' ),
								'maxlength' => 2,
							),
							array(
								'key' => 'field_feature_title',
								'label' => __( 'Title', 'flatwp' ),
								'name' => 'title',
								'type' => 'text',
								'required' => 1,
								'maxlength' => 50,
							),
							array(
								'key' => 'field_feature_description',
								'label' => __( 'Description', 'flatwp' ),
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
				'label' => '📝 ' . __( 'Content Section', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_content_heading',
						'label' => __( 'Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_content_text',
						'label' => __( 'Content', 'flatwp' ),
						'name' => 'content',
						'type' => 'wysiwyg',
						'instructions' => __( 'Rich text content with formatting', 'flatwp' ),
						'toolbar' => 'full',
						'media_upload' => 1,
					),
					array(
						'key' => 'field_content_image',
						'label' => __( 'Image (Optional)', 'flatwp' ),
						'name' => 'image',
						'type' => 'image',
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
					array(
						'key' => 'field_content_layout',
						'label' => __( 'Layout', 'flatwp' ),
						'name' => 'layout',
						'type' => 'select',
						'choices' => array(
							'single' => __( 'Full Width', 'flatwp' ),
							'two-column' => __( 'Two Columns', 'flatwp' ),
						),
						'default_value' => 'single',
					),
				),
			),

			// Pricing Block.
			'pricing' => array(
				'key' => 'layout_pricing',
				'name' => 'pricing',
				'label' => '💰 ' . __( 'Pricing Table', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_pricing_heading',
						'label' => __( 'Section Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_pricing_plans',
						'label' => __( 'Pricing Plans', 'flatwp' ),
						'name' => 'plans',
						'type' => 'repeater',
						'instructions' => __( 'Add 2-3 pricing tiers', 'flatwp' ),
						'layout' => 'block',
						'button_label' => __( 'Add Plan', 'flatwp' ),
						'sub_fields' => array(
							array(
								'key' => 'field_plan_name',
								'label' => __( 'Plan Name', 'flatwp' ),
								'name' => 'name',
								'type' => 'text',
								'required' => 1,
								'maxlength' => 30,
							),
							array(
								'key' => 'field_plan_price',
								'label' => __( 'Price', 'flatwp' ),
								'name' => 'price',
								'type' => 'text',
								'instructions' => __( 'e.g., $29/month', 'flatwp' ),
								'required' => 1,
								'maxlength' => 20,
							),
							array(
								'key' => 'field_plan_features',
								'label' => __( 'Features', 'flatwp' ),
								'name' => 'features',
								'type' => 'textarea',
								'instructions' => __( 'One feature per line', 'flatwp' ),
								'rows' => 5,
							),
							array(
								'key' => 'field_plan_cta_text',
								'label' => __( 'Button Text', 'flatwp' ),
								'name' => 'cta_text',
								'type' => 'text',
								'default_value' => __( 'Get Started', 'flatwp' ),
								'maxlength' => 30,
							),
							array(
								'key' => 'field_plan_cta_url',
								'label' => __( 'Button URL', 'flatwp' ),
								'name' => 'cta_url',
								'type' => 'url',
							),
							array(
								'key' => 'field_plan_featured',
								'label' => __( 'Featured Plan', 'flatwp' ),
								'name' => 'featured',
								'type' => 'true_false',
								'instructions' => __( 'Highlight this plan', 'flatwp' ),
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
				'label' => '💬 ' . __( 'Testimonials', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_testimonials_heading',
						'label' => __( 'Section Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'maxlength' => 100,
					),
					array(
						'key' => 'field_testimonials_items',
						'label' => __( 'Testimonials', 'flatwp' ),
						'name' => 'testimonials',
						'type' => 'repeater',
						'instructions' => __( 'Add 3-6 testimonials', 'flatwp' ),
						'layout' => 'block',
						'button_label' => __( 'Add Testimonial', 'flatwp' ),
						'sub_fields' => array(
							array(
								'key' => 'field_testimonial_quote',
								'label' => __( 'Quote', 'flatwp' ),
								'name' => 'quote',
								'type' => 'textarea',
								'required' => 1,
								'rows' => 3,
								'maxlength' => 300,
							),
							array(
								'key' => 'field_testimonial_author',
								'label' => __( 'Author Name', 'flatwp' ),
								'name' => 'author',
								'type' => 'text',
								'required' => 1,
								'maxlength' => 50,
							),
							array(
								'key' => 'field_testimonial_role',
								'label' => __( 'Role/Company', 'flatwp' ),
								'name' => 'role',
								'type' => 'text',
								'maxlength' => 100,
							),
							array(
								'key' => 'field_testimonial_avatar',
								'label' => __( 'Avatar (Optional)', 'flatwp' ),
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
				'label' => '📣 ' . __( 'Call-to-Action - Simple', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_cta_simple_heading',
						'label' => __( 'Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_cta_simple_subheading',
						'label' => __( 'Subheading', 'flatwp' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 2,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_cta_simple_button_text',
						'label' => __( 'Button Text', 'flatwp' ),
						'name' => 'button_text',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 30,
					),
					array(
						'key' => 'field_cta_simple_button_url',
						'label' => __( 'Button URL', 'flatwp' ),
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
				'label' => '🎁 ' . __( 'Call-to-Action - Boxed', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_cta_boxed_heading',
						'label' => __( 'Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 100,
					),
					array(
						'key' => 'field_cta_boxed_subheading',
						'label' => __( 'Subheading', 'flatwp' ),
						'name' => 'subheading',
						'type' => 'textarea',
						'rows' => 2,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_cta_boxed_button_text',
						'label' => __( 'Button Text', 'flatwp' ),
						'name' => 'button_text',
						'type' => 'text',
						'required' => 1,
						'maxlength' => 30,
					),
					array(
						'key' => 'field_cta_boxed_button_url',
						'label' => __( 'Button URL', 'flatwp' ),
						'name' => 'button_url',
						'type' => 'url',
						'required' => 1,
					),
					array(
						'key' => 'field_cta_boxed_background_color',
						'label' => __( 'Background Style', 'flatwp' ),
						'name' => 'background_color',
						'type' => 'select',
						'choices' => array(
							'gradient-blue' => __( 'Blue Gradient', 'flatwp' ),
							'gradient-purple' => __( 'Purple Gradient', 'flatwp' ),
							'gradient-green' => __( 'Green Gradient', 'flatwp' ),
							'solid-dark' => __( 'Solid Dark', 'flatwp' ),
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
				'label' => '📝 ' . __( 'Content Widget', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_sidebar_content_title',
						'label' => __( 'Title', 'flatwp' ),
						'name' => 'title',
						'type' => 'text',
						'instructions' => __( 'Widget title (optional)', 'flatwp' ),
						'required' => 0,
						'maxlength' => 60,
					),
					array(
						'key' => 'field_sidebar_content_content',
						'label' => __( 'Content', 'flatwp' ),
						'name' => 'content',
						'type' => 'wysiwyg',
						'instructions' => __( 'Widget content with text, images, and links', 'flatwp' ),
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
				'label' => '📣 ' . __( 'CTA Widget', 'flatwp' ),
				'display' => 'block',
				'sub_fields' => array(
					array(
						'key' => 'field_sidebar_cta_heading',
						'label' => __( 'Heading', 'flatwp' ),
						'name' => 'heading',
						'type' => 'text',
						'instructions' => __( 'Call to action heading', 'flatwp' ),
						'required' => 1,
						'maxlength' => 50,
					),
					array(
						'key' => 'field_sidebar_cta_text',
						'label' => __( 'Text', 'flatwp' ),
						'name' => 'text',
						'type' => 'textarea',
						'instructions' => __( 'Supporting text for the CTA', 'flatwp' ),
						'required' => 0,
						'rows' => 3,
						'maxlength' => 150,
					),
					array(
						'key' => 'field_sidebar_cta_button_text',
						'label' => __( 'Button Text', 'flatwp' ),
						'name' => 'button_text',
						'type' => 'text',
						'instructions' => __( 'Button label', 'flatwp' ),
						'required' => 1,
						'default_value' => __( 'Learn More', 'flatwp' ),
						'maxlength' => 30,
					),
					array(
						'key' => 'field_sidebar_cta_button_url',
						'label' => __( 'Button URL', 'flatwp' ),
						'name' => 'button_url',
						'type' => 'url',
						'instructions' => __( 'Where the button links to', 'flatwp' ),
						'required' => 1,
					),
				),
			),
		);

		return apply_filters( 'flatwp_sidebar_block_layouts', $layouts );
	}

	/**
	 * Add page template selection meta box.
	 */
	public function add_template_meta_box() {
		add_meta_box(
			'flatwp_page_template',
			__( 'FlatWP Page Template', 'flatwp' ),
			array( $this, 'render_template_meta_box' ),
			'page',
			'side',
			'high'
		);
	}

	/**
	 * Render page template selection meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_template_meta_box( $post ) {
		$current_template = get_post_meta( $post->ID, self::TEMPLATE_META_KEY, true );
		wp_nonce_field( 'flatwp_template_meta_box', 'flatwp_template_meta_box_nonce' );

		?>
		<div class="flatwp-template-selector">
			<p>
				<label for="flatwp_page_template_select">
					<strong><?php esc_html_e( 'Choose a Starter Template', 'flatwp' ); ?></strong>
				</label>
			</p>
			<select id="flatwp_page_template_select" name="flatwp_page_template" class="widefat">
				<option value=""><?php esc_html_e( 'None (Custom Layout)', 'flatwp' ); ?></option>
				<?php foreach ( $this->templates as $key => $template ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_template, $key ); ?>>
						<?php echo esc_html( $template['icon'] . ' ' . $template['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<div class="flatwp-template-descriptions" style="margin-top: 12px;">
				<?php foreach ( $this->templates as $key => $template ) : ?>
					<p class="description flatwp-template-desc" data-template="<?php echo esc_attr( $key ); ?>" style="display: none;">
						<strong><?php echo esc_html( $template['label'] ); ?></strong><br>
						<?php echo esc_html( $template['description'] ); ?>
					</p>
				<?php endforeach; ?>
			</div>

			<p style="margin-top: 12px;">
				<button type="button" id="flatwp_apply_template" class="button button-secondary" style="width: 100%;">
					<?php esc_html_e( 'Apply Template Blocks', 'flatwp' ); ?>
				</button>
			</p>
			<p class="description">
				<?php esc_html_e( 'Selecting a template will pre-populate content blocks below. You can customize them after applying.', 'flatwp' ); ?>
			</p>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Show/hide template descriptions.
			function updateTemplateDescription() {
				var selected = $('#flatwp_page_template_select').val();
				$('.flatwp-template-desc').hide();
				if (selected) {
					$('.flatwp-template-desc[data-template="' + selected + '"]').show();
				}
			}

			$('#flatwp_page_template_select').on('change', updateTemplateDescription);
			updateTemplateDescription();

			// Apply template AJAX handler.
			$('#flatwp_apply_template').on('click', function() {
				var template = $('#flatwp_page_template_select').val();
				if (!template) {
					alert('<?php esc_html_e( 'Please select a template first.', 'flatwp' ); ?>');
					return;
				}

				if (!confirm('<?php esc_html_e( 'This will replace existing blocks. Continue?', 'flatwp' ); ?>')) {
					return;
				}

				var button = $(this);
				button.prop('disabled', true).text('<?php esc_html_e( 'Applying...', 'flatwp' ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'flatwp_apply_template',
						post_id: <?php echo (int) $post->ID; ?>,
						template: template,
						nonce: '<?php echo wp_create_nonce( 'flatwp_apply_template' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							alert('<?php esc_html_e( 'Template applied! Refreshing page...', 'flatwp' ); ?>');
							location.reload();
						} else {
							alert('<?php esc_html_e( 'Error:', 'flatwp' ); ?> ' + response.data.message);
							button.prop('disabled', false).text('<?php esc_html_e( 'Apply Template Blocks', 'flatwp' ); ?>');
						}
					},
					error: function() {
						alert('<?php esc_html_e( 'AJAX error occurred.', 'flatwp' ); ?>');
						button.prop('disabled', false).text('<?php esc_html_e( 'Apply Template Blocks', 'flatwp' ); ?>');
					}
				});
			});
		});
		</script>

		<style>
			.flatwp-template-selector .button {
				margin-top: 4px;
			}
			.flatwp-template-desc {
				padding: 8px;
				background: #f0f0f1;
				border-left: 3px solid #2271b1;
				margin: 8px 0;
			}
		</style>
		<?php
	}

	/**
	 * Save template selection meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_template_meta_box( $post_id, $post ) {
		// Verify nonce.
		if ( ! isset( $_POST['flatwp_template_meta_box_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['flatwp_template_meta_box_nonce'], 'flatwp_template_meta_box' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save template selection.
		if ( isset( $_POST['flatwp_page_template'] ) ) {
			$template = sanitize_key( $_POST['flatwp_page_template'] );
			if ( '' === $template ) {
				delete_post_meta( $post_id, self::TEMPLATE_META_KEY );
			} else {
				update_post_meta( $post_id, self::TEMPLATE_META_KEY, $template );
			}
		}
	}

	/**
	 * AJAX handler for applying template blocks.
	 */
	public function ajax_apply_template() {
		check_ajax_referer( 'flatwp_apply_template', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$template = isset( $_POST['template'] ) ? sanitize_key( $_POST['template'] ) : '';

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'flatwp' ) ) );
		}

		if ( ! isset( $this->templates[ $template ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid template.', 'flatwp' ) ) );
		}

		// Get template blocks.
		$template_data = $this->templates[ $template ];
		$blocks = $template_data['blocks'];

		// Create ACF flexible content data structure.
		$flexible_content = array();
		foreach ( $blocks as $index => $block_name ) {
			$flexible_content[] = array(
				'acf_fc_layout' => $block_name,
				// Add default/placeholder data for each block type.
				...$this->get_default_block_data( $block_name )
			);
		}

		// Update the flexible content field.
		update_field( 'flexible_content', $flexible_content, $post_id );

		wp_send_json_success( array( 'message' => __( 'Template applied successfully.', 'flatwp' ) ) );
	}

	/**
	 * Get default/placeholder data for a block type.
	 *
	 * @param string $block_name Block layout name.
	 * @return array Default field values.
	 */
	private function get_default_block_data( $block_name ) {
		$defaults = array(
			'hero_centered' => array(
				'heading' => __( 'Welcome to Your Site', 'flatwp' ),
				'subheading' => __( 'Start building something amazing with FlatWP', 'flatwp' ),
				'cta_text' => __( 'Get Started', 'flatwp' ),
				'cta_url' => '#',
			),
			'hero_split' => array(
				'heading' => __( 'Modern Headless WordPress', 'flatwp' ),
				'subheading' => __( 'Fast, scalable, and developer-friendly', 'flatwp' ),
				'image_position' => 'right',
				'cta_text' => __( 'Learn More', 'flatwp' ),
				'cta_url' => '#',
			),
			'features_grid' => array(
				'heading' => __( 'Features', 'flatwp' ),
				'subheading' => __( 'Everything you need to build modern web applications', 'flatwp' ),
				'features' => array(
					array(
						'icon' => '⚡',
						'title' => __( 'Lightning Fast', 'flatwp' ),
						'description' => __( 'Built for speed with Next.js and ISR', 'flatwp' ),
					),
					array(
						'icon' => '🎨',
						'title' => __( 'Beautiful Design', 'flatwp' ),
						'description' => __( 'Modern UI with TailwindCSS', 'flatwp' ),
					),
					array(
						'icon' => '🔒',
						'title' => __( 'Secure by Default', 'flatwp' ),
						'description' => __( 'Best security practices built-in', 'flatwp' ),
					),
				),
			),
			'content_section' => array(
				'heading' => __( 'About Us', 'flatwp' ),
				'content' => '<p>' . __( 'Add your content here...', 'flatwp' ) . '</p>',
				'layout' => 'single',
			),
			'pricing' => array(
				'heading' => __( 'Pricing Plans', 'flatwp' ),
				'plans' => array(
					array(
						'name' => __( 'Starter', 'flatwp' ),
						'price' => '$29/mo',
						'features' => "Feature 1\nFeature 2\nFeature 3",
						'cta_text' => __( 'Get Started', 'flatwp' ),
						'cta_url' => '#',
						'featured' => false,
					),
					array(
						'name' => __( 'Professional', 'flatwp' ),
						'price' => '$99/mo',
						'features' => "All Starter features\nFeature 4\nFeature 5",
						'cta_text' => __( 'Get Started', 'flatwp' ),
						'cta_url' => '#',
						'featured' => true,
					),
				),
			),
			'testimonials' => array(
				'heading' => __( 'What Our Customers Say', 'flatwp' ),
				'testimonials' => array(
					array(
						'quote' => __( 'This is an amazing product! Highly recommended.', 'flatwp' ),
						'author' => 'John Doe',
						'role' => __( 'CEO, Company Inc.', 'flatwp' ),
					),
				),
			),
			'cta_simple' => array(
				'heading' => __( 'Ready to Get Started?', 'flatwp' ),
				'subheading' => __( 'Join thousands of satisfied customers today', 'flatwp' ),
				'button_text' => __( 'Get Started', 'flatwp' ),
				'button_url' => '#',
			),
			'cta_boxed' => array(
				'heading' => __( 'Start Building Today', 'flatwp' ),
				'subheading' => __( 'Get started with FlatWP in minutes', 'flatwp' ),
				'button_text' => __( 'Get Started Free', 'flatwp' ),
				'button_url' => '#',
				'background_color' => 'gradient-blue',
			),
		);

		return isset( $defaults[ $block_name ] ) ? $defaults[ $block_name ] : array();
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only on post edit screen.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		// Only for pages.
		global $post;
		if ( ! $post || 'page' !== $post->post_type ) {
			return;
		}

		// Enqueue jQuery (WordPress includes it).
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Get available templates.
	 *
	 * @return array Templates array.
	 */
	public function get_templates() {
		return $this->templates;
	}
}
