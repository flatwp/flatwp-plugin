<?php
/**
 * FlatWP Blocks Registration
 *
 * Handles registration and rendering of all FlatWP Gutenberg blocks.
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FlatWP_Blocks
 *
 * Registers and manages FlatWP custom Gutenberg blocks.
 */
class FlatWP_Blocks {

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'flatwp';

	/**
	 * List of blocks to register.
	 *
	 * @var array
	 */
	private $blocks = array(
		'hero',
		'hero-minimal',
		'cta',
		'section',
		'features',
		'feature-item',
		'faq',
		'faq-item',
		'statistics',
		'stat-item',
		'team',
		'team-member',
		'image-text',
		'icon-text',
		'logo-row',
		'card',
		'pricing-column',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		// If init already fired (class instantiated late), register blocks directly.
		// Otherwise, hook into init action.
		if ( did_action( 'init' ) ) {
			$this->register_blocks();
		} else {
			add_action( 'init', array( $this, 'register_blocks' ) );
		}

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
	}

	/**
	 * Register custom block category.
	 *
	 * @param array                   $categories Block categories.
	 * @param WP_Block_Editor_Context $context    Block editor context.
	 * @return array Modified categories.
	 */
	public function register_block_category( $categories, $context ) {
		return array_merge(
			array(
				array(
					'slug'  => 'flatwp',
					'title' => __( 'FlatWP Blocks', 'flatwp-headless-connector' ),
					'icon'  => 'layout',
				),
			),
			$categories
		);
	}

	/**
	 * Register all blocks.
	 */
	public function register_blocks() {
		foreach ( $this->blocks as $block ) {
			$this->register_block( $block );
		}
	}

	/**
	 * Register a single block.
	 *
	 * @param string $block_name Block name without namespace.
	 */
	private function register_block( $block_name ) {
		// Try build directory first (for production), then src (for development)
		$build_dir = FLATWP_PLUGIN_DIR . 'build/blocks/' . $block_name;
		$src_dir   = FLATWP_PLUGIN_DIR . 'src/blocks/' . $block_name;

		// Use build directory if it exists, otherwise fall back to src
		$block_dir = file_exists( $build_dir . '/block.json' ) ? $build_dir : $src_dir;

		// Check if block.json exists
		if ( ! file_exists( $block_dir . '/block.json' ) ) {
			return;
		}

		// Register the block
		register_block_type( $block_dir );
	}

	/**
	 * Enqueue editor assets.
	 */
	public function enqueue_editor_assets() {
		$asset_file = FLATWP_PLUGIN_DIR . 'build/index.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;
		} else {
			$asset = array(
				'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor' ),
				'version'      => FLATWP_VERSION,
			);
		}

		// Enqueue editor script
		$editor_script = FLATWP_PLUGIN_DIR . 'build/index.js';
		if ( file_exists( $editor_script ) ) {
			wp_enqueue_script(
				'flatwp-blocks-editor',
				FLATWP_PLUGIN_URL . 'build/index.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
		}

		// Enqueue editor styles
		wp_enqueue_style(
			'flatwp-blocks-editor',
			FLATWP_PLUGIN_URL . 'blocks/editor.css',
			array( 'wp-edit-blocks' ),
			FLATWP_VERSION
		);
	}

	/**
	 * Enqueue frontend styles.
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style(
			'flatwp-blocks',
			FLATWP_PLUGIN_URL . 'blocks/style.css',
			array(),
			FLATWP_VERSION
		);
	}

	/**
	 * Get SVG icon by name.
	 *
	 * @param string $icon_name Icon name.
	 * @param string $class     Optional CSS class.
	 * @return string SVG markup.
	 */
	public static function get_icon( $icon_name, $class = '' ) {
		$icons = self::get_icons();

		if ( ! isset( $icons[ $icon_name ] ) ) {
			$icon_name = 'star';
		}

		$class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';

		return '<svg' . $class_attr . ' xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icons[ $icon_name ] . '</svg>';
	}

	/**
	 * Get all available icons.
	 *
	 * @return array Icon SVG paths.
	 */
	public static function get_icons() {
		return array(
			'zap'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
			'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
			'rocket'   => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>',
			'target'   => '<circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle>',
			'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
			'trending' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>',
			'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
			'heart'    => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>',
			'settings' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>',
			'globe'    => '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
			'code'     => '<polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline>',
			'layers'   => '<polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline>',
			'check'    => '<polyline points="20 6 9 17 4 12"></polyline>',
			'arrow'    => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',
			'mail'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
			'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>',
			'clock'    => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
			'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
			'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>',
			'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line>',
			'search'   => '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>',
			'lock'     => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
			'unlock'   => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path>',
			'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>',
			'server'   => '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line>',
			'cloud'    => '<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>',
			'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle>',
			'twitter'  => '<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>',
			'x'        => '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>',
			'plus'     => '<line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>',
			'minus'    => '<line x1="5" y1="12" x2="19" y2="12"></line>',
			'chevron-down' => '<polyline points="6 9 12 15 18 9"></polyline>',
			'chevron-up'   => '<polyline points="18 15 12 9 6 15"></polyline>',
			'chevron-right' => '<polyline points="9 18 15 12 9 6"></polyline>',
			'chevron-left'  => '<polyline points="15 18 9 12 15 6"></polyline>',
		);
	}

	/**
	 * Get padding value from size name.
	 *
	 * @param string $size Padding size name.
	 * @return string CSS padding value.
	 */
	public static function get_padding_value( $size ) {
		$sizes = array(
			'none' => '0',
			'sm'   => '1.5rem',
			'md'   => '3rem',
			'lg'   => '5rem',
			'xl'   => '8rem',
		);

		return isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['lg'];
	}

	/**
	 * Get container width value from size name.
	 *
	 * @param string $size Width size name.
	 * @return string CSS max-width value.
	 */
	public static function get_container_width( $size ) {
		$sizes = array(
			'narrow'  => '48rem',
			'default' => '72rem',
			'wide'    => '90rem',
			'full'    => '100%',
		);

		return isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['default'];
	}

	/**
	 * Get border radius value from size name.
	 *
	 * @param string $size Radius size name.
	 * @return string CSS border-radius value.
	 */
	public static function get_border_radius( $size ) {
		$sizes = array(
			'none' => '0',
			'sm'   => '0.125rem',
			'md'   => '0.375rem',
			'lg'   => '0.5rem',
			'xl'   => '0.75rem',
			'2xl'  => '1rem',
			'full' => '9999px',
		);

		return isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['lg'];
	}

	/**
	 * Get shadow value from size name.
	 *
	 * @param string $size Shadow size name.
	 * @return string CSS box-shadow value.
	 */
	public static function get_shadow( $size ) {
		$shadows = array(
			'none' => 'none',
			'sm'   => '0 1px 2px 0 rgb(0 0 0 / 0.05)',
			'md'   => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
			'lg'   => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
			'xl'   => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
		);

		return isset( $shadows[ $size ] ) ? $shadows[ $size ] : $shadows['md'];
	}

	/**
	 * Get gradient direction CSS value.
	 *
	 * @param string $direction Direction name.
	 * @return string CSS gradient direction.
	 */
	public static function get_gradient_direction( $direction ) {
		$directions = array(
			'to-t'  => 'to top',
			'to-b'  => 'to bottom',
			'to-l'  => 'to left',
			'to-r'  => 'to right',
			'to-tr' => 'to top right',
			'to-tl' => 'to top left',
			'to-br' => 'to bottom right',
			'to-bl' => 'to bottom left',
		);

		return isset( $directions[ $direction ] ) ? $directions[ $direction ] : $directions['to-r'];
	}

	/**
	 * Get color scheme styles.
	 *
	 * @param string $scheme    Color scheme name.
	 * @param bool   $is_text   Whether to return text color instead of background.
	 * @return array CSS properties.
	 */
	public static function get_color_scheme_styles( $scheme, $is_text = false ) {
		$schemes = array(
			'light' => array(
				'background' => '#ffffff',
				'text'       => '#1f2937',
				'muted'      => '#6b7280',
			),
			'dark' => array(
				'background' => '#111827',
				'text'       => '#f9fafb',
				'muted'      => '#9ca3af',
			),
			'primary' => array(
				'background' => '#3b82f6',
				'text'       => '#ffffff',
				'muted'      => '#bfdbfe',
			),
			'secondary' => array(
				'background' => '#6366f1',
				'text'       => '#ffffff',
				'muted'      => '#c7d2fe',
			),
			'transparent' => array(
				'background' => 'transparent',
				'text'       => 'inherit',
				'muted'      => 'inherit',
			),
		);

		return isset( $schemes[ $scheme ] ) ? $schemes[ $scheme ] : $schemes['light'];
	}

	/**
	 * Generate SVG divider markup.
	 *
	 * @param string $type     Divider type.
	 * @param string $position Position (top or bottom).
	 * @param string $color    Divider color.
	 * @return string SVG markup.
	 */
	public static function get_divider( $type, $position = 'top', $color = '#ffffff' ) {
		if ( 'none' === $type ) {
			return '';
		}

		$flip = 'top' === $position ? '' : ' style="transform: rotate(180deg);"';

		$dividers = array(
			'wave'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"' . $flip . '><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="' . esc_attr( $color ) . '"></path></svg>',
			'curve'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"' . $flip . '><path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" fill="' . esc_attr( $color ) . '"></path></svg>',
			'triangle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"' . $flip . '><path d="M598.97 114.72L0 0 0 120 1200 120 1200 0 598.97 114.72z" fill="' . esc_attr( $color ) . '"></path></svg>',
			'slant'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"' . $flip . '><path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" fill="' . esc_attr( $color ) . '"></path></svg>',
		);

		if ( ! isset( $dividers[ $type ] ) ) {
			return '';
		}

		$position_class = 'top' === $position ? 'flatwp-divider--top' : 'flatwp-divider--bottom';

		return '<div class="flatwp-divider ' . $position_class . '">' . $dividers[ $type ] . '</div>';
	}
}
