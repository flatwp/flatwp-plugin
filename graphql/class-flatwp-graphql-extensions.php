<?php
/**
 * GraphQL Extensions Class
 *
 * Extends WPGraphQL with custom fields for FlatWP features.
 *
 * @package FlatWP
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP GraphQL Extensions Class
 */
class FlatWP_GraphQL_Extensions {

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
		// Only initialize if WPGraphQL is active.
		if ( ! class_exists( 'WPGraphQL' ) ) {
			add_action( 'admin_notices', array( $this, 'wpgraphql_not_active_notice' ) );
			return;
		}

		// Register custom fields.
		add_action( 'graphql_register_types', array( $this, 'register_seo_fields' ) );
		// Featured post field no longer needed - using WordPress built-in isSticky field
		// add_action( 'graphql_register_types', array( $this, 'register_featured_post_field' ) );
		add_action( 'graphql_register_types', array( $this, 'register_revalidate_time_field' ) );
		add_action( 'graphql_register_types', array( $this, 'register_page_settings_field' ) );
		add_action( 'graphql_register_types', array( $this, 'register_settings_query' ) );
		add_action( 'graphql_register_types', array( $this, 'register_search_index_query' ) );

		// Log GraphQL queries
		add_filter( 'graphql_request_results', array( $this, 'log_graphql_query' ), 10, 5 );
	}

	/**
	 * Log GraphQL query execution
	 *
	 * @param mixed  $response GraphQL response.
	 * @param mixed  $schema Schema.
	 * @param string $operation_name Operation name.
	 * @param string $query GraphQL query string.
	 * @param mixed  $variables Query variables.
	 * @return mixed Unchanged response.
	 */
	public function log_graphql_query( $response, $schema, $operation_name, $query, $variables ) {
		// Skip introspection queries
		if ( $this->is_introspection_query( $query ) ) {
			return $response;
		}

		// Extract operation type from query
		$operation_type = 'query'; // default
		if ( preg_match( '/^\s*(query|mutation|subscription)\s+/i', $query, $matches ) ) {
			$operation_type = strtolower( $matches[1] );
		}

		// Extract root field names
		$fields = $this->extract_root_fields( $query );
		$operation_name = $operation_name ?: 'anonymous';

		// Log the query
		if ( flatwp()->logger ) {
			flatwp()->logger->log_graphql(
				sprintf( 'GraphQL %s: %s', $operation_type, $operation_name ),
				array(
					'operation' => $operation_name,
					'type' => $operation_type,
					'fields' => $fields,
					'field_count' => count( $fields ),
					'has_variables' => ! empty( $variables ),
				)
			);
		}

		return $response;
	}

	/**
	 * Check if query is introspection
	 *
	 * @param string $query GraphQL query.
	 * @return bool
	 */
	private function is_introspection_query( $query ) {
		return strpos( $query, '__schema' ) !== false || strpos( $query, '__type' ) !== false;
	}

	/**
	 * Extract root field names from query
	 *
	 * @param string $query GraphQL query.
	 * @return array List of root fields.
	 */
	private function extract_root_fields( $query ) {
		$fields = array();

		// Remove comments
		$query = preg_replace( '/#[^\n]*/', '', $query );

		// Simple extraction of top-level fields after opening brace
		if ( preg_match_all( '/\{[^{]*?([a-zA-Z_][a-zA-Z0-9_]*)/s', $query, $matches ) ) {
			$keywords = array( 'query', 'mutation', 'subscription', 'fragment', 'on' );
			foreach ( $matches[1] as $field ) {
				if ( ! in_array( strtolower( $field ), $keywords, true ) ) {
					$fields[] = $field;
				}
			}
		}

		return array_unique( array_slice( $fields, 0, 10 ) ); // Max 10 fields
	}

	/**
	 * Show admin notice if WPGraphQL is not active.
	 */
	public function wpgraphql_not_active_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'FlatWP Companion:', 'flatwp-headless-connector' ); ?></strong>
				<?php
				printf(
					/* translators: %s: WPGraphQL plugin link */
					esc_html__( 'WPGraphQL plugin is required for GraphQL features. %s', 'flatwp-headless-connector' ),
					'<a href="https://wordpress.org/plugins/wp-graphql/" target="_blank">' . esc_html__( 'Install WPGraphQL', 'flatwp-headless-connector' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Register SEO metadata fields for Post and Page types.
	 */
	public function register_seo_fields() {
		// Only if SEO integration is enabled.
		if ( ! flatwp()->get_setting( 'enable_seo_integration', true ) ) {
			return;
		}

		// Skip if RankMath or Yoast SEO is active (they register their own seo fields).
		if ( class_exists( 'RankMath' ) || defined( 'WPSEO_VERSION' ) ) {
			return;
		}

		// Register SEO type.
		register_graphql_object_type( 'SEOMetadata', array(
			'description' => __( 'SEO metadata for posts and pages', 'flatwp-headless-connector' ),
			'fields'      => array(
				'title'       => array(
					'type'        => 'String',
					'description' => __( 'SEO title', 'flatwp-headless-connector' ),
				),
				'description' => array(
					'type'        => 'String',
					'description' => __( 'SEO description', 'flatwp-headless-connector' ),
				),
				'canonical'   => array(
					'type'        => 'String',
					'description' => __( 'Canonical URL', 'flatwp-headless-connector' ),
				),
				'robots'      => array(
					'type'        => 'String',
					'description' => __( 'Robots meta tag', 'flatwp-headless-connector' ),
				),
			),
		) );

		// Register Open Graph type.
		register_graphql_object_type( 'OpenGraphMetadata', array(
			'description' => __( 'Open Graph metadata', 'flatwp-headless-connector' ),
			'fields'      => array(
				'title'       => array(
					'type'        => 'String',
					'description' => __( 'Open Graph title', 'flatwp-headless-connector' ),
				),
				'description' => array(
					'type'        => 'String',
					'description' => __( 'Open Graph description', 'flatwp-headless-connector' ),
				),
				'image'       => array(
					'type'        => 'String',
					'description' => __( 'Open Graph image URL', 'flatwp-headless-connector' ),
				),
			),
		) );

		// Register Twitter Card type.
		register_graphql_object_type( 'TwitterCardMetadata', array(
			'description' => __( 'Twitter Card metadata', 'flatwp-headless-connector' ),
			'fields'      => array(
				'title'       => array(
					'type'        => 'String',
					'description' => __( 'Twitter Card title', 'flatwp-headless-connector' ),
				),
				'description' => array(
					'type'        => 'String',
					'description' => __( 'Twitter Card description', 'flatwp-headless-connector' ),
				),
				'image'       => array(
					'type'        => 'String',
					'description' => __( 'Twitter Card image URL', 'flatwp-headless-connector' ),
				),
			),
		) );

		// Register complete SEO data type.
		register_graphql_object_type( 'SEO', array(
			'description' => __( 'Complete SEO metadata', 'flatwp-headless-connector' ),
			'fields'      => array(
				'meta'    => array(
					'type'        => 'SEOMetadata',
					'description' => __( 'Basic SEO metadata', 'flatwp-headless-connector' ),
				),
				'og'      => array(
					'type'        => 'OpenGraphMetadata',
					'description' => __( 'Open Graph metadata', 'flatwp-headless-connector' ),
				),
				'twitter' => array(
					'type'        => 'TwitterCardMetadata',
					'description' => __( 'Twitter Card metadata', 'flatwp-headless-connector' ),
				),
				'schema'  => array(
					'type'        => 'String',
					'description' => __( 'JSON-LD structured data', 'flatwp-headless-connector' ),
				),
			),
		) );

		// Add SEO field to content types.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $post_types as $post_type ) {
			$graphql_type = ucfirst( $post_type );

			register_graphql_field( $graphql_type, 'seo', array(
				'type'        => 'SEO',
				'description' => __( 'SEO metadata from installed SEO plugin or defaults', 'flatwp-headless-connector' ),
				'resolve'     => function( $post ) {
					$seo_integration = flatwp()->seo_integration;
					if ( ! $seo_integration ) {
						return null;
					}

					// Get metadata.
					$metadata = $seo_integration->get_seo_metadata( $post->ID );

					// Build return structure.
					$seo_data = array(
						'meta' => array(
							'title'       => isset( $metadata['title'] ) ? $metadata['title'] : null,
							'description' => isset( $metadata['description'] ) ? $metadata['description'] : null,
							'canonical'   => isset( $metadata['canonical'] ) ? $metadata['canonical'] : null,
							'robots'      => isset( $metadata['robots'] ) ? $metadata['robots'] : null,
						),
					);

					// Add Open Graph if available.
					if ( isset( $metadata['og'] ) ) {
						$seo_data['og'] = $metadata['og'];
					}

					// Add Twitter Card if available.
					if ( isset( $metadata['twitter'] ) ) {
						$seo_data['twitter'] = $metadata['twitter'];
					}

					// Add structured data.
					$schema = $seo_integration->get_structured_data( $post->ID );
					if ( $schema ) {
						$seo_data['schema'] = wp_json_encode( $schema );
					}

					return $seo_data;
				},
			) );
		}
	}

	/**
	 * Register featured post field for Post type.
	 */
	public function register_featured_post_field() {
		// Register boolean field for featured status.
		register_graphql_field( 'Post', 'isFeatured', array(
			'type'        => 'Boolean',
			'description' => __( 'Whether this post is marked as featured', 'flatwp-headless-connector' ),
			'resolve'     => function( $post ) {
				return (bool) get_post_meta( $post->ID, '_is_featured', true );
			},
		) );
	}

	/**
	 * Register revalidate time field for content types.
	 */
	public function register_revalidate_time_field() {
		$cache_manager = flatwp()->cache_manager;
		if ( ! $cache_manager ) {
			return;
		}

		// Register for all public post types.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $post_types as $post_type ) {
			$graphql_type = ucfirst( $post_type );

			register_graphql_field( $graphql_type, 'revalidateTime', array(
				'type'        => 'Int',
				'description' => __( 'ISR revalidate time in seconds for Next.js caching', 'flatwp-headless-connector' ),
				'resolve'     => function( $post ) use ( $cache_manager ) {
					return $cache_manager->get_revalidate_time( $post->ID, get_post_type( $post->ID ) );
				},
			) );
		}
	}

	/**
	 * Register page settings field for Page type.
	 */
	public function register_page_settings_field() {
		$page_settings = flatwp()->page_settings;
		if ( ! $page_settings ) {
			return;
		}

		// Register FlatWPPageSettings type.
		register_graphql_object_type( 'FlatWPPageSettings', array(
			'description' => __( 'FlatWP page-specific settings', 'flatwp-headless-connector' ),
			'fields'      => array(
				'hideTitle' => array(
					'type'        => 'Boolean',
					'description' => __( 'Whether to hide the page title', 'flatwp-headless-connector' ),
				),
				'containerWidth' => array(
					'type'        => 'String',
					'description' => __( 'Container width setting: contained or full-width', 'flatwp-headless-connector' ),
				),
				'hideHeader' => array(
					'type'        => 'Boolean',
					'description' => __( 'Whether to hide the site header', 'flatwp-headless-connector' ),
				),
				'hideFooter' => array(
					'type'        => 'Boolean',
					'description' => __( 'Whether to hide the site footer', 'flatwp-headless-connector' ),
				),
				'customCssClass' => array(
					'type'        => 'String',
					'description' => __( 'Custom CSS classes for the page', 'flatwp-headless-connector' ),
				),
			),
		) );

		// Add flatwpSettings field to Page type.
		register_graphql_field( 'Page', 'flatwpSettings', array(
			'type'        => 'FlatWPPageSettings',
			'description' => __( 'FlatWP page settings', 'flatwp-headless-connector' ),
			'resolve'     => function( $page ) use ( $page_settings ) {
				return $page_settings->get_settings( $page->ID );
			},
		) );

		// Register ACF flexible content field for sidebar blocks.
		// This leverages WPGraphQL for ACF plugin to expose the sidebar_blocks field.
		// The actual field registration happens in ACF, this just documents it.
		// WPGraphQL for ACF automatically exposes ACF fields to GraphQL.
	}

	/**
	 * Register settings query.
	 */
	public function register_settings_query() {
		register_graphql_object_type( 'FlatWPSettings', array(
			'description' => __( 'FlatWP plugin settings', 'flatwp-headless-connector' ),
			'fields'      => array(
				'version'           => array(
					'type'        => 'String',
					'description' => __( 'Plugin version', 'flatwp-headless-connector' ),
				),
				'enablePreview'     => array(
					'type'        => 'Boolean',
					'description' => __( 'Whether preview mode is enabled', 'flatwp-headless-connector' ),
				),
				'enableBlurPlaceholder' => array(
					'type'        => 'Boolean',
					'description' => __( 'Whether blur placeholders are enabled', 'flatwp-headless-connector' ),
				),
				'enableSearchIndex' => array(
					'type'        => 'Boolean',
					'description' => __( 'Whether search index is enabled', 'flatwp-headless-connector' ),
				),
				'searchIndexUrl'    => array(
					'type'        => 'String',
					'description' => __( 'URL to search index JSON file', 'flatwp-headless-connector' ),
				),
				'seoPlugin'         => array(
					'type'        => 'String',
					'description' => __( 'Active SEO plugin', 'flatwp-headless-connector' ),
				),
			),
		) );

		register_graphql_field( 'RootQuery', 'flatwpSettings', array(
			'type'        => 'FlatWPSettings',
			'description' => __( 'Get FlatWP plugin settings', 'flatwp-headless-connector' ),
			'resolve'     => function() {
				$settings = array(
					'version'              => FLATWP_VERSION,
					'enablePreview'        => flatwp()->get_setting( 'enable_preview', true ),
					'enableBlurPlaceholder' => flatwp()->get_setting( 'enable_blur_placeholder', true ),
					'enableSearchIndex'    => flatwp()->get_setting( 'enable_search_index', false ),
				);

				// Add search index URL if enabled.
				if ( $settings['enableSearchIndex'] ) {
					$search_index = flatwp()->search_index;
					if ( $search_index ) {
						$settings['searchIndexUrl'] = $search_index->get_index_url();
					}
				}

				// Add SEO plugin info.
				$seo_integration = flatwp()->seo_integration;
				if ( $seo_integration ) {
					$settings['seoPlugin'] = $seo_integration->get_active_plugin();
				}

				return $settings;
			},
		) );
	}

	/**
	 * Register search index query.
	 */
	public function register_search_index_query() {
		// Only if search index is enabled.
		if ( ! flatwp()->get_setting( 'enable_search_index', false ) ) {
			return;
		}

		register_graphql_object_type( 'SearchIndexMetadata', array(
			'description' => __( 'Search index metadata', 'flatwp-headless-connector' ),
			'fields'      => array(
				'url'       => array(
					'type'        => 'String',
					'description' => __( 'URL to search index JSON file', 'flatwp-headless-connector' ),
				),
				'version'   => array(
					'type'        => 'String',
					'description' => __( 'Index version/timestamp', 'flatwp-headless-connector' ),
				),
				'generated' => array(
					'type'        => 'String',
					'description' => __( 'When the index was generated', 'flatwp-headless-connector' ),
				),
				'postCount' => array(
					'type'        => 'Int',
					'description' => __( 'Number of posts in the index', 'flatwp-headless-connector' ),
				),
				'fileSize'  => array(
					'type'        => 'Int',
					'description' => __( 'Index file size in bytes', 'flatwp-headless-connector' ),
				),
			),
		) );

		register_graphql_field( 'RootQuery', 'searchIndexMetadata', array(
			'type'        => 'SearchIndexMetadata',
			'description' => __( 'Get search index metadata', 'flatwp-headless-connector' ),
			'resolve'     => function() {
				$search_index = flatwp()->search_index;
				if ( ! $search_index ) {
					return null;
				}

				return $search_index->get_index_metadata();
			},
		) );
	}

	/**
	 * Get GraphQL endpoint URL.
	 *
	 * @return string
	 */
	public function get_endpoint_url() {
		if ( ! function_exists( 'get_graphql_endpoint' ) ) {
			return home_url( '/graphql' );
		}

		return get_graphql_endpoint();
	}

	/**
	 * Check if WPGraphQL is active.
	 *
	 * @return bool
	 */
	public function is_wpgraphql_active() {
		return class_exists( 'WPGraphQL' );
	}
}
