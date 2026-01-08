<?php
/**
 * FlatWP Parameter Registry Class
 *
 * Manages preview URL parameters with flexible, extensible parameter resolution.
 * Inspired by HWP Previews plugin architecture.
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Parameter_Registry {
	/**
	 * Singleton instance
	 *
	 * @var FlatWP_Parameter_Registry|null
	 */
	private static ?FlatWP_Parameter_Registry $instance = null;

	/**
	 * Registered parameters array
	 *
	 * @var array<string, callable>
	 */
	private array $parameters = [];

	/**
	 * Private constructor for singleton pattern
	 */
	private function __construct() {
		$this->register_default_parameters();
	}

	/**
	 * Get singleton instance
	 *
	 * @return FlatWP_Parameter_Registry
	 */
	public static function get_instance(): FlatWP_Parameter_Registry {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register default preview parameters
	 */
	private function register_default_parameters(): void {
		// Post ID parameter
		$this->register( 'ID', static fn( WP_Post $post ) => (string) $post->ID );

		// Post slug parameter
		$this->register( 'slug', static fn( WP_Post $post ) => $post->post_name );

		// Post type parameter
		$this->register( 'type', static fn( WP_Post $post ) => $post->post_type );

		// Post status parameter
		$this->register( 'status', static fn( WP_Post $post ) => $post->post_status );

		// Post author ID parameter
		$this->register( 'author_ID', static fn( WP_Post $post ) => (string) $post->post_author );

		// Parent post ID parameter (for hierarchical post types)
		$this->register( 'parent_ID', static fn( WP_Post $post ) => (string) $post->post_parent );

		// Post publish date (YYYY-MM-DD format)
		$this->register( 'date', static fn( WP_Post $post ) => gmdate( 'Y-m-d', strtotime( $post->post_date ) ) );

		// Post year (YYYY)
		$this->register( 'year', static fn( WP_Post $post ) => gmdate( 'Y', strtotime( $post->post_date ) ) );

		// Post month (MM)
		$this->register( 'month', static fn( WP_Post $post ) => gmdate( 'm', strtotime( $post->post_date ) ) );

		// Post day (DD)
		$this->register( 'day', static fn( WP_Post $post ) => gmdate( 'd', strtotime( $post->post_date ) ) );

		// Primary category slug (for posts)
		$this->register(
			'category',
			static function( WP_Post $post ): string {
				if ( 'post' !== $post->post_type ) {
					return 'uncategorized';
				}
				$categories = get_the_category( $post->ID );
				return ! empty( $categories ) ? $categories[0]->slug : 'uncategorized';
			}
		);

		// Post author username
		$this->register(
			'author',
			static function( WP_Post $post ): string {
				$author = get_userdata( $post->post_author );
				return $author ? $author->user_nicename : '';
			}
		);

		// Allow plugins/theme to register custom parameters
		do_action( 'flatwp_register_preview_parameters', $this );
	}

	/**
	 * Register a custom preview parameter
	 *
	 * @param string   $name     Parameter name (used in {name} placeholder)
	 * @param callable $callback Function that takes WP_Post and returns string value
	 */
	public function register( string $name, callable $callback ): self {
		if ( empty( $name ) ) {
			return $this;
		}

		$this->parameters[ $name ] = $callback;
		return $this;
	}

	/**
	 * Unregister a parameter
	 *
	 * @param string $name Parameter name
	 */
	public function unregister( string $name ): self {
		if ( isset( $this->parameters[ $name ] ) ) {
			unset( $this->parameters[ $name ] );
		}
		return $this;
	}

	/**
	 * Resolve a parameter value for a post
	 *
	 * @param string  $name Parameter name
	 * @param WP_Post $post Post object
	 * @return string|null Parameter value or null if not found
	 */
	public function resolve( string $name, WP_Post $post ): ?string {
		if ( ! isset( $this->parameters[ $name ] ) ) {
			return null;
		}

		try {
			$value = call_user_func( $this->parameters[ $name ], $post );
			return (string) $value;
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Get all registered parameter names
	 *
	 * @return string[]
	 */
	public function get_parameter_names(): array {
		return array_keys( $this->parameters );
	}

	/**
	 * Check if a parameter is registered
	 *
	 * @param string $name Parameter name
	 * @return bool
	 */
	public function has( string $name ): bool {
		return isset( $this->parameters[ $name ] );
	}
}
