<?php
/**
 * FlatWP Gutenberg Blocks
 *
 * Handles registration and management of native Gutenberg blocks.
 *
 * @package FlatWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FlatWP_Gutenberg_Blocks
 *
 * Registers and manages native Gutenberg blocks for FlatWP.
 */
class FlatWP_Gutenberg_Blocks {

	/**
	 * Blocks directory path.
	 *
	 * @var string
	 */
	private $blocks_dir;

	/**
	 * Available blocks.
	 *
	 * @var array
	 */
	private $blocks = array(
		// Layout blocks
		'section',
		'card',
		// Hero & CTA
		'hero',
		'hero-minimal',
		'cta',
		// Content blocks
		'features',
		'feature-item',
		'faq',
		'faq-item',
		'icon-text',
		'logo-row',
		// Team & Stats
		'statistics',
		'stat-item',
		'team',
		'team-member',
		// Legacy
		'image-text',
		'pricing-column',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->blocks_dir = FLATWP_PLUGIN_DIR . 'build/blocks/';
		$this->init();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init() {
		$this->register_block_category();
		$this->register_blocks();
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register all FlatWP blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		if ( ! is_dir( $this->blocks_dir ) ) {
			$this->blocks_dir = FLATWP_PLUGIN_DIR . 'src/blocks/';
		}

		foreach ( $this->blocks as $block ) {
			$block_path = $this->blocks_dir . $block;

			if ( is_dir( $block_path ) && file_exists( $block_path . '/block.json' ) ) {
				register_block_type( $block_path );
			}
		}
	}

	/**
	 * Register FlatWP block category.
	 *
	 * @return void
	 */
	public function register_block_category() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_filter(
			'block_categories_all',
			function ( $categories ) {
				foreach ( $categories as $category ) {
					if ( 'flatwp' === $category['slug'] ) {
						return $categories;
					}
				}

				array_unshift(
					$categories,
					array(
						'slug'  => 'flatwp',
						'title' => __( 'FlatWP', 'flatwp-wordpress-connector' ),
						'icon'  => 'layout',
					)
				);

				return $categories;
			}
		);
	}

	/**
	 * Enqueue editor-specific assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		// Block-specific scripts/styles are automatically enqueued via block.json
	}

	/**
	 * Get block attributes for a specific block.
	 *
	 * @param string $block_name Block name (without namespace).
	 * @return array|null Block attributes or null if not found.
	 */
	public function get_block_attributes( $block_name ) {
		$block_json_path = $this->blocks_dir . $block_name . '/block.json';

		if ( ! file_exists( $block_json_path ) ) {
			return null;
		}

		$block_json = file_get_contents( $block_json_path );
		$block_data = json_decode( $block_json, true );

		return $block_data['attributes'] ?? null;
	}

	/**
	 * Get all registered FlatWP blocks.
	 *
	 * @return array List of block names.
	 */
	public function get_blocks() {
		return $this->blocks;
	}

	/**
	 * Parse Gutenberg blocks from post content.
	 *
	 * @param string $content Post content.
	 * @return array Parsed FlatWP blocks with their attributes.
	 */
	public function parse_flatwp_blocks( $content ) {
		$blocks        = parse_blocks( $content );
		$flatwp_blocks = array();

		foreach ( $blocks as $block ) {
			if ( $this->is_flatwp_block( $block ) ) {
				$flatwp_blocks[] = $this->normalize_block( $block );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$flatwp_blocks = array_merge(
					$flatwp_blocks,
					$this->parse_inner_blocks( $block['innerBlocks'] )
				);
			}
		}

		return $flatwp_blocks;
	}

	/**
	 * Check if a block is a FlatWP block.
	 *
	 * @param array $block Block data.
	 * @return bool True if FlatWP block.
	 */
	private function is_flatwp_block( $block ) {
		return isset( $block['blockName'] ) && strpos( $block['blockName'], 'flatwp/' ) === 0;
	}

	/**
	 * Normalize block data for GraphQL.
	 *
	 * @param array $block Block data.
	 * @return array Normalized block.
	 */
	private function normalize_block( $block ) {
		$block_name = str_replace( 'flatwp/', '', $block['blockName'] );

		return array(
			'name'       => $block_name,
			'attributes' => $block['attrs'] ?? array(),
		);
	}

	/**
	 * Parse inner blocks recursively.
	 *
	 * @param array $inner_blocks Inner blocks array.
	 * @return array FlatWP blocks found in inner blocks.
	 */
	private function parse_inner_blocks( $inner_blocks ) {
		$flatwp_blocks = array();

		foreach ( $inner_blocks as $block ) {
			if ( $this->is_flatwp_block( $block ) ) {
				$flatwp_blocks[] = $this->normalize_block( $block );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$flatwp_blocks = array_merge(
					$flatwp_blocks,
					$this->parse_inner_blocks( $block['innerBlocks'] )
				);
			}
		}

		return $flatwp_blocks;
	}
}
