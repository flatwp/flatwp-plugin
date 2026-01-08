<?php
/**
 * FlatWP Preview Class
 *
 * Manages preview functionality with flexible URL patterns and token-based validation.
 * Inspired by HWP Previews plugin architecture with parameter registry support.
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Preview {
	/**
	 * Parameter registry instance
	 *
	 * @var FlatWP_Parameter_Registry
	 */
	private FlatWP_Parameter_Registry $registry;

	/**
	 * URL resolver instance
	 *
	 * @var FlatWP_Preview_URL_Resolver
	 */
	private FlatWP_Preview_URL_Resolver $resolver;

	/**
	 * Preview token storage in WordPress transients
	 * Format: flatwp_preview_token_{token_hash}
	 *
	 * @var string
	 */
	private const TOKEN_TRANSIENT_PREFIX = 'flatwp_preview_token_';

	/**
	 * Token expiration time in seconds (default: 1 hour)
	 *
	 * @var int
	 */
	private int $token_expiration = 3600;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->registry = FlatWP_Parameter_Registry::get_instance();
		$this->resolver = new FlatWP_Preview_URL_Resolver( $this->registry );

		// Set token expiration from settings if available
		if ( function_exists( 'flatwp' ) ) {
			$expiration = flatwp()->get_setting( 'preview_token_expiration', 3600 );
			$this->token_expiration = (int) $expiration;
		}

		add_filter( 'preview_post_link', array( $this, 'modify_preview_link' ), 10, 2 );
		add_filter( 'post_link', array( $this, 'modify_post_link' ), 10, 2 );
		add_filter( 'page_link', array( $this, 'modify_page_link' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'modify_post_type_link' ), 10, 2 );
		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );
	}

	/**
	 * Modify preview link to use Next.js frontend
	 *
	 * Uses token-based validation and flexible URL patterns with parameter registry.
	 *
	 * @param string         $link Preview link URL.
	 * @param WP_Post|int    $post Post object or post ID.
	 * @return string Modified preview link.
	 */
	public function modify_preview_link( $link, $post ) {
		// Convert post ID to post object if needed
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return $link;
		}

		if ( ! function_exists( 'flatwp' ) ) {
			return $link;
		}

		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );
		$secret = flatwp()->get_setting( 'api_secret' );

		if ( empty( $nextjs_url ) || empty( $secret ) ) {
			return $link;
		}

		// Generate unique preview token
		$token = $this->generate_preview_token( $post->ID );

		// Get preview URL pattern from settings
		// Default pattern: /api/preview?secret={secret}&token={token}&id={ID}&type={type}&slug={slug}
		$preview_pattern = flatwp()->get_setting(
			'preview_url_pattern',
			'/api/preview?secret={secret}&token={token}&id={ID}&type={type}&slug={slug}'
		);

		// Build base URL with secret and token
		$base_url = str_replace(
			array( '{secret}', '{token}' ),
			array( $secret, $token ),
			$preview_pattern
		);

		// Resolve remaining placeholders using parameter registry
		$resolved_url = $this->resolver->resolve( $base_url, $post );

		return trailingslashit( $nextjs_url ) . ltrim( $resolved_url, '/' );
	}

	/**
	 * Get the correct slug for preview
	 *
	 * For draft/auto-draft posts, generate slug from current title.
	 * For published posts, use the post_name.
	 *
	 * @param WP_Post $post Post object.
	 * @return string Post slug.
	 */
	private function get_preview_slug( $post ) {
		// For published posts, use the finalized post_name
		if ( $post->post_status === 'publish' ) {
			return $post->post_name;
		}

		// For drafts, if post_name is empty or doesn't match title, regenerate
		if ( empty( $post->post_name ) || $post->post_status === 'auto-draft' ) {
			return sanitize_title( $post->post_title );
		}

		// Check if the current post_name matches what the title would generate
		$title_slug = sanitize_title( $post->post_title );

		// If they match, use post_name (it's already correct)
		if ( $post->post_name === $title_slug ) {
			return $post->post_name;
		}

		// If title has changed, generate fresh slug from current title
		// This handles the case where draft title was updated after initial save
		return $title_slug;
	}

	/**
	 * Generate a preview token for a post
	 *
	 * Creates a cryptographically secure token and stores it in WordPress transients
	 * with automatic expiration.
	 *
	 * @param int $post_id Post ID.
	 * @return string Preview token.
	 */
	private function generate_preview_token( int $post_id ): string {
		// Generate a cryptographically secure random token
		$token = wp_generate_password( 32, false );

		// Store token metadata
		$token_data = array(
			'post_id'    => $post_id,
			'created_at' => time(),
			'expires_at' => time() + $this->token_expiration,
		);

		// Store in transient with auto-expiration
		set_transient(
			self::TOKEN_TRANSIENT_PREFIX . $token,
			$token_data,
			$this->token_expiration
		);

		return $token;
	}

	/**
	 * Validate a preview token
	 *
	 * Checks if a token exists, hasn't expired, and is valid.
	 *
	 * @param string $token Preview token.
	 * @return array|false Token data if valid, false otherwise.
	 */
	public function validate_preview_token( string $token ) {
		if ( empty( $token ) ) {
			return false;
		}

		// Retrieve token data from transient
		$token_data = get_transient( self::TOKEN_TRANSIENT_PREFIX . $token );

		if ( false === $token_data ) {
			return false;
		}

		// Verify token hasn't expired (double-check in case transient didn't expire)
		if ( isset( $token_data['expires_at'] ) && time() > $token_data['expires_at'] ) {
			delete_transient( self::TOKEN_TRANSIENT_PREFIX . $token );
			return false;
		}

		return $token_data;
	}

	/**
	 * Revoke a preview token
	 *
	 * Immediately invalidates a token, preventing further use.
	 *
	 * @param string $token Preview token.
	 * @return bool True if revoked, false if token didn't exist.
	 */
	public function revoke_preview_token( string $token ): bool {
		return (bool) delete_transient( self::TOKEN_TRANSIENT_PREFIX . $token );
	}

	/**
	 * Modify post link to use Next.js frontend
	 *
	 * @param string         $link Post permalink URL.
	 * @param WP_Post|int    $post Post object or post ID.
	 * @return string Modified post link.
	 */
	public function modify_post_link( $link, $post ) {
		return $this->get_frontend_url( $post, $link );
	}

	/**
	 * Modify page link to use Next.js frontend
	 *
	 * @param string         $link Page permalink URL.
	 * @param WP_Post|int    $post Post object or post ID.
	 * @return string Modified page link.
	 */
	public function modify_page_link( $link, $post ) {
		return $this->get_frontend_url( $post, $link );
	}

	/**
	 * Modify custom post type link to use Next.js frontend
	 *
	 * @param string         $link Post type permalink URL.
	 * @param WP_Post|int    $post Post object or post ID.
	 * @return string Modified post type link.
	 */
	public function modify_post_type_link( $link, $post ) {
		return $this->get_frontend_url( $post, $link );
	}

	/**
	 * Get the frontend URL for a post
	 *
	 * @param WP_Post|int $post          Post object or post ID.
	 * @param string      $fallback_link Fallback WordPress permalink.
	 * @return string Frontend URL or fallback.
	 */
	private function get_frontend_url( $post, $fallback_link ) {
		// Convert post ID to post object if needed
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return $fallback_link;
		}

		// Only modify for admin and REST API contexts
		if ( ! is_admin() && ! defined( 'REST_REQUEST' ) ) {
			return $fallback_link;
		}

		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );

		if ( empty( $nextjs_url ) ) {
			return $fallback_link;
		}

		// Check if frontend URL override is enabled
		$enable_frontend_urls = flatwp()->get_setting( 'enable_frontend_urls', true );
		if ( ! $enable_frontend_urls ) {
			return $fallback_link;
		}

		// Get the post type
		$post_type = $post->post_type;

		// Get URL pattern for this post type
		$url_pattern = $this->get_url_pattern_for_post_type( $post_type );

		if ( empty( $url_pattern ) ) {
			// Fallback: use WordPress slug structure
			return $this->build_default_frontend_url( $nextjs_url, $post );
		}

		// Build URL from pattern
		return $this->build_url_from_pattern( $nextjs_url, $post, $url_pattern );
	}

	/**
	 * Get URL pattern for a specific post type
	 *
	 * @param string $post_type Post type.
	 * @return string URL pattern.
	 */
	private function get_url_pattern_for_post_type( $post_type ) {
		$patterns = flatwp()->get_setting( 'frontend_url_patterns', array() );

		if ( isset( $patterns[ $post_type ] ) ) {
			return $patterns[ $post_type ];
		}

		// Default patterns
		$defaults = array(
			'post' => '/blog/{slug}',
			'page' => '/{slug}',
		);

		return isset( $defaults[ $post_type ] ) ? $defaults[ $post_type ] : '/{slug}';
	}

	/**
	 * Build URL from pattern
	 *
	 * @param string  $base_url Next.js base URL.
	 * @param WP_Post $post     Post object.
	 * @param string  $pattern  URL pattern.
	 * @return string Complete URL.
	 */
	private function build_url_from_pattern( $base_url, $post, $pattern ) {
		$replacements = array(
			'{slug}'     => $post->post_name,
			'{id}'       => $post->ID,
			'{type}'     => $post->post_type,
			'{year}'     => get_the_date( 'Y', $post ),
			'{month}'    => get_the_date( 'm', $post ),
			'{day}'      => get_the_date( 'd', $post ),
		);

		// Handle category in pattern
		if ( strpos( $pattern, '{category}' ) !== false ) {
			$categories = get_the_category( $post->ID );
			$category_slug = ! empty( $categories ) ? $categories[0]->slug : 'uncategorized';
			$replacements['{category}'] = $category_slug;
		}

		// Handle author in pattern
		if ( strpos( $pattern, '{author}' ) !== false ) {
			$author = get_userdata( $post->post_author );
			$replacements['{author}'] = $author ? $author->user_nicename : '';
		}

		$url_path = str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$pattern
		);

		return trailingslashit( $base_url ) . ltrim( $url_path, '/' );
	}

	/**
	 * Build default frontend URL
	 *
	 * @param string  $base_url Next.js base URL.
	 * @param WP_Post $post     Post object.
	 * @return string Complete URL.
	 */
	private function build_default_frontend_url( $base_url, $post ) {
		$post_type = $post->post_type;
		$slug = $post->post_name;

		if ( $post_type === 'post' ) {
			return trailingslashit( $base_url ) . 'blog/' . $slug;
		}

		if ( $post_type === 'page' ) {
			return trailingslashit( $base_url ) . $slug;
		}

		// For custom post types
		return trailingslashit( $base_url ) . $post_type . '/' . $slug;
	}

	/**
	 * Register REST API endpoints for preview validation
	 *
	 * Endpoints:
	 * - POST /wp-json/flatwp/v1/preview/validate - Validate preview token
	 */
	public function register_rest_endpoints(): void {
		register_rest_route(
			'flatwp/v1',
			'/preview/validate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_validate_preview_token' ),
				'permission_callback' => '__return_true', // Public endpoint, validation happens via secret
				'args'                => array(
					'token' => array(
						'type'        => 'string',
						'required'    => true,
						'description' => 'Preview token to validate',
					),
				),
			)
		);
	}

	/**
	 * REST endpoint: Validate preview token
	 *
	 * Returns token data if valid, error if invalid or expired.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return WP_REST_Response Response object.
	 */
	public function rest_validate_preview_token( WP_REST_Request $request ): WP_REST_Response {
		$token = $request->get_param( 'token' );

		if ( empty( $token ) ) {
			return new WP_REST_Response(
				array( 'error' => 'Token parameter is required' ),
				400
			);
		}

		$token_data = $this->validate_preview_token( $token );

		if ( ! $token_data ) {
			return new WP_REST_Response(
				array( 'error' => 'Invalid or expired preview token' ),
				401
			);
		}

		// Return token data to Next.js
		return new WP_REST_Response(
			array(
				'valid'      => true,
				'post_id'    => (int) $token_data['post_id'],
				'created_at' => (int) $token_data['created_at'],
				'expires_at' => (int) $token_data['expires_at'],
			),
			200
		);
	}
}
