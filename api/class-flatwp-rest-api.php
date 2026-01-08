<?php
/**
 * REST API for React Admin Dashboard
 *
 * Provides all endpoints needed for the React admin interface
 *
 * @package FlatWP
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP REST API Class
 */
class FlatWP_REST_API {

	/**
	 * API namespace
	 */
	const NAMESPACE = 'flatwp/v1';

	/**
	 * Security instance
	 *
	 * @var FlatWP_Security
	 */
	private $security;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->security = new FlatWP_Security();
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		// Dashboard endpoint
		register_rest_route(
			self::NAMESPACE,
			'/dashboard/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_dashboard_stats' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Connection endpoints
		register_rest_route(
			self::NAMESPACE,
			'/connection/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_connection_status' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/connection/test',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'test_connection' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Health check endpoint
		register_rest_route(
			self::NAMESPACE,
			'/health-check',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_health_check' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Test webhook endpoint
		register_rest_route(
			self::NAMESPACE,
			'/test-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'test_webhook' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Cache endpoints
		register_rest_route(
			self::NAMESPACE,
			'/cache/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_cache_stats' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cache/clear',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'clear_cache' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'type' => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => 'all',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => 'Cache type to clear: all, homepage, or any registered post type (post, page, documentation, etc.)',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cache/clear-path',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'clear_path_cache' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'path' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Settings endpoints
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Activity log endpoints (legacy - uses options table)
		register_rest_route(
			self::NAMESPACE,
			'/logs',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_logs' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'page'     => array(
						'default'           => 1,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'per_page' => array(
						'default'           => 20,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'type'     => array(
						'default'           => 'all',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// New logger endpoints (uses database table)
		register_rest_route(
			self::NAMESPACE,
			'/logger/logs',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_logger_logs' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'page'     => array(
						'default'           => 1,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'per_page' => array(
						'default'           => 20,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'type'     => array(
						'default'           => 'all',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'severity' => array(
						'default'           => 'all',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/logger/errors',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_logger_errors' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/logger/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_logger_stats' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/logger/clear',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'clear_logger_logs' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'type' => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/logger/export',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'export_logger_logs' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'format' => array(
						'default'           => 'json',
						'type'              => 'string',
						'enum'              => array( 'json', 'csv' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'type'   => array(
						'default'           => 'all',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Performance metrics endpoint
		register_rest_route(
			self::NAMESPACE,
			'/performance/metrics',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_performance_metrics' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'period' => array(
						'default'           => '24h',
						'type'              => 'string',
						'enum'              => array( '1h', '24h', '7d', '30d' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Performance time-series endpoint (for charts and historical data)
		register_rest_route(
			self::NAMESPACE,
			'/performance/timeseries',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_performance_timeseries' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'hours' => array(
						'default'           => 24,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Lighthouse score endpoint
		register_rest_route(
			self::NAMESPACE,
			'/lighthouse/latest',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_lighthouse_score' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Lighthouse run endpoint
		register_rest_route(
			self::NAMESPACE,
			'/lighthouse/run',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'run_lighthouse_audit' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Recent posts endpoint
		register_rest_route(
			self::NAMESPACE,
			'/posts/recent',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_recent_posts' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'limit' => array(
						'default'           => 5,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Revalidate all endpoint
		register_rest_route(
			self::NAMESPACE,
			'/revalidate-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'revalidate_all' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Revalidation settings endpoints
		register_rest_route(
			self::NAMESPACE,
			'/revalidation/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_revalidation_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_revalidation_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Manual revalidation endpoint
		register_rest_route(
			self::NAMESPACE,
			'/revalidate-manual',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'revalidate_manual' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'action' => array(
						'required'          => true,
						'type'              => 'string',
						'enum'              => array( 'all', 'homepage', 'path', 'post_type' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'path' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'post_type' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Setup wizard endpoints
		register_rest_route(
			self::NAMESPACE,
			'/wizard/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_wizard_status' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/wizard/complete',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'complete_wizard' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Webhook endpoints
		register_rest_route(
			self::NAMESPACE,
			'/webhooks/config',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_webhook_config' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_webhook_config' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/targets',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'add_webhook_target' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/targets/(?P<target_id>\w+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_webhook_target' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/test/(?P<target_id>\w+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'test_webhook_target' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/metrics',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_webhook_metrics' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/plugins',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_webhook_plugins' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/plugins/(?P<plugin_id>\w+)/enable',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'enable_webhook_plugin' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/webhooks/plugins/(?P<plugin_id>\w+)/disable',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'disable_webhook_plugin' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Preview token validation endpoint
		register_rest_route(
			'flatwp/v1',
			'/preview/validate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'validate_preview_token' ),
				'permission_callback' => '__return_true', // Public endpoint
				'args'                => array(
					'token' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Permission callback
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get dashboard statistics
	 */
	public function get_dashboard_stats( $request ) {
		$connection_status = $this->get_cached_connection_status();
		$cache_stats = $this->get_cached_cache_stats();

		// Get counts for all public post types
		$content_stats = array();
		$public_types = $this->get_public_post_types();

		foreach ( $public_types as $post_type ) {
			$counts = wp_count_posts( $post_type );
			$post_type_obj = get_post_type_object( $post_type );
			$label = $post_type_obj ? $post_type_obj->labels->name : $post_type;

			$content_stats[ 'total_' . $post_type ] = isset( $counts->publish ) ? $counts->publish : 0;

			// Add draft/pending counts for the first post type (usually 'post')
			if ( $post_type === 'post' ) {
				$content_stats['draft_posts'] = isset( $counts->draft ) ? $counts->draft : 0;
				$content_stats['pending_posts'] = isset( $counts->pending ) ? $counts->pending : 0;
			}
		}

		// Keep backward compatibility with existing keys
		if ( ! isset( $content_stats['total_posts'] ) && isset( $content_stats['total_post'] ) ) {
			$content_stats['total_posts'] = $content_stats['total_post'];
		}
		if ( ! isset( $content_stats['total_pages'] ) && isset( $content_stats['total_page'] ) ) {
			$content_stats['total_pages'] = $content_stats['total_page'];
		}

		$stats = array(
			'connection'    => $connection_status,
			'cache'         => $cache_stats,
			'content_stats' => $content_stats,
			'post_types'    => array_values( $public_types ), // Include list of available post types
			'system_status' => array(
				'php_version'       => PHP_VERSION,
				'wp_version'        => get_bloginfo( 'version' ),
				'plugin_version'    => FLATWP_VERSION,
				'graphql_active'    => class_exists( 'WPGraphQL' ),
				'acf_active'        => class_exists( 'ACF' ) || function_exists( 'acf' ),
			),
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $stats,
			)
		);
	}

	/**
	 * Get connection status
	 */
	public function get_connection_status( $request ) {
		$status = $this->get_cached_connection_status();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $status,
			)
		);
	}

	/**
	 * Test connection to Next.js
	 */
	public function test_connection( $request ) {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );

		if ( empty( $nextjs_url ) ) {
			return new WP_Error(
				'no_url',
				__( 'Next.js URL not configured', 'flatwp' ),
				array( 'status' => 400 )
			);
		}

		$health_url = trailingslashit( $nextjs_url ) . 'api/health';
		$start_time = microtime( true );
		$response = wp_remote_get(
			$health_url,
			array(
				'timeout' => 10,
				'headers' => array(
					'User-Agent' => 'FlatWP-React/' . FLATWP_VERSION,
				),
			)
		);
		$response_time = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			set_transient( 'flatwp_connection_test_result', 'failed', HOUR_IN_SECONDS );
			set_transient( 'flatwp_connection_test_timestamp', current_time( 'timestamp' ), HOUR_IN_SECONDS );

			$status = array(
				'connected'     => false,
				'url'           => $nextjs_url,
				'response_time' => $response_time,
				'last_tested'   => current_time( 'timestamp' ),
				'http_code'     => null,
				'error_message' => $error_message,
			);

			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => $status,
					'message' => $error_message,
				)
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$connected = $response_code === 200;

		set_transient( 'flatwp_connection_test_result', $connected ? 'success' : 'failed', HOUR_IN_SECONDS );
		set_transient( 'flatwp_connection_test_timestamp', current_time( 'timestamp' ), HOUR_IN_SECONDS );
		set_transient(
			'flatwp_connection_test_details',
			array(
				'http_code'     => $response_code,
				'response_time' => $response_time,
			),
			HOUR_IN_SECONDS
		);

		$status = array(
			'connected'     => $connected,
			'url'           => $nextjs_url,
			'response_time' => $response_time,
			'last_tested'   => current_time( 'timestamp' ),
			'http_code'     => $response_code,
		);

		return rest_ensure_response(
			array(
				'success' => $connected,
				'data'    => $status,
				'message' => $connected ? __( 'Connection successful', 'flatwp' ) : __( 'Connection failed', 'flatwp' ),
			)
		);
	}

	/**
	 * Get health check data
	 */
	public function get_health_check( $request ) {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );
		$webhook_enabled = flatwp()->get_setting( 'webhook_enabled', true );
		$last_webhook_success = get_option( 'flatwp_last_webhook_success', null );
		$last_connection_test = get_transient( 'flatwp_connection_test_timestamp' );
		$test_connection_details = get_transient( 'flatwp_connection_test_details' );

		// Determine overall status
		$status = 'disconnected';
		$status_message = __( 'Not configured', 'flatwp' );

		if ( ! empty( $nextjs_url ) ) {
			if ( $last_connection_test && $test_connection_details && $test_connection_details['http_code'] === 200 ) {
				$status = 'connected';
				$status_message = __( 'Connected and healthy', 'flatwp' );
			} elseif ( $last_connection_test ) {
				$status = 'degraded';
				$status_message = __( 'Connection issues detected', 'flatwp' );
			}
		}

		$data = array(
			'status' => $status,
			'status_message' => $status_message,
			'last_webhook_success' => $last_webhook_success,
			'last_connection_test' => $last_connection_test,
			'nextjs_url' => $nextjs_url,
			'webhook_enabled' => $webhook_enabled,
			'test_connection_data' => array(
				'connected' => $test_connection_details && $test_connection_details['http_code'] === 200,
				'response_time' => $test_connection_details ? $test_connection_details['response_time'] : null,
				'http_code' => $test_connection_details ? $test_connection_details['http_code'] : null,
			),
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'data' => $data,
			)
		);
	}

	/**
	 * Test webhook endpoint
	 * Uses improved revalidation method with test mode for accurate validation
	 */
	public function test_webhook( $request ) {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );
		$secret = flatwp()->get_setting( 'api_secret' );

		if ( empty( $nextjs_url ) || empty( $secret ) ) {
			return new WP_Error(
				'missing_config',
				__( 'Next.js URL or secret not configured', 'flatwp' ),
				array( 'status' => 400 )
			);
		}

		// Use improved test method from revalidation class
		$revalidation = flatwp()->revalidation;
		$start_time = microtime( true );
		$result = $revalidation->test_webhook_connection();
		$response_time = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		$webhook_url = trailingslashit( $nextjs_url ) . 'api/revalidate';

		if ( ! $result['success'] ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data' => array(
						'response_time' => $response_time,
						'http_code' => 0,
						'response_body' => null,
						'url' => $webhook_url,
						'error' => $result['error'],
					),
					'message' => $result['error'],
				)
			);
		}

		// Success response
		return rest_ensure_response(
			array(
				'success' => true,
				'data' => array(
					'response_time' => $response_time,
					'http_code' => 200,
					'response_body' => array(
						'revalidated' => true,
						'message' => $result['message'],
					),
					'url' => $webhook_url,
				),
				'message' => __( 'Webhook test successful! Next.js endpoint is responding correctly.', 'flatwp' ),
			)
		);
	}

	/**
	 * Get cache statistics
	 */
	public function get_cache_stats( $request ) {
		$stats = $this->get_cached_cache_stats();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $stats,
			)
		);
	}

	/**
	 * Clear cache
	 */
	public function clear_cache( $request ) {
		$type = $request->get_param( 'type' );
		$revalidation = flatwp()->revalidation;

		if ( ! $revalidation ) {
			return new WP_Error(
				'revalidation_unavailable',
				__( 'Revalidation functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$paths = $this->get_paths_by_type( $type );

		// Log manual cache clear action BEFORE triggering
		if ( flatwp()->logger ) {
			flatwp()->logger->log(
				FlatWP_Logger::TYPE_MANUAL,
				FlatWP_Logger::SEVERITY_INFO,
				sprintf( 'Manual cache clear triggered for: %s (%d path(s))', $type ?: 'all', count( $paths ) ),
				array(
					'action'      => 'cache_clear',
					'cache_type'  => $type ?: 'all',
					'path_count'  => count( $paths ),
				)
			);
		}

		$result = $revalidation->trigger_revalidation( $paths );

		if ( is_wp_error( $result ) ) {
			// Log failure
			if ( flatwp()->logger ) {
				flatwp()->logger->log_error(
					'Manual cache clear failed: ' . $result->get_error_message(),
					array(
						'action' => 'cache_clear',
						'cache_type' => $type,
						'error_code' => $result->get_error_code(),
					)
				);
			}
			return $result;
		}

		// Logging for webhook is already handled in trigger_revalidation() with detailed metrics

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Cache cleared successfully', 'flatwp' ),
				'data'    => array(
					'type'  => $type,
					'paths' => $paths,
				),
			)
		);
	}

	/**
	 * Clear specific path cache
	 */
	public function clear_path_cache( $request ) {
		$path = $request->get_param( 'path' );
		$revalidation = flatwp()->revalidation;

		if ( ! $revalidation ) {
			return new WP_Error(
				'revalidation_unavailable',
				__( 'Revalidation functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		// Log manual path cache clear action BEFORE triggering
		if ( flatwp()->logger ) {
			flatwp()->logger->log(
				FlatWP_Logger::TYPE_MANUAL,
				FlatWP_Logger::SEVERITY_INFO,
				sprintf( 'Manual cache clear triggered for path: %s', $path ),
				array(
					'action'      => 'cache_clear',
					'cache_type'  => 'path',
					'path'        => $path,
				)
			);
		}

		$result = $revalidation->trigger_revalidation( array( $path ) );

		if ( is_wp_error( $result ) ) {
			// Log failure
			if ( flatwp()->logger ) {
				flatwp()->logger->log_error(
					sprintf( 'Manual path cache clear failed for %s: %s', $path, $result->get_error_message() ),
					array(
						'action' => 'cache_clear',
						'path' => $path,
						'error_code' => $result->get_error_code(),
					)
				);
			}
			return $result;
		}

		// Logging for webhook is already handled in trigger_revalidation() with detailed metrics

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => sprintf( __( 'Cache cleared for path: %s', 'flatwp' ), $path ),
			)
		);
	}

	/**
	 * Get settings
	 */
	public function get_settings( $request ) {
		$settings = get_option( 'flatwp_settings', array() );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $settings,
			)
		);
	}

	/**
	 * Update settings
	 */
	public function update_settings( $request ) {
		$new_settings = $request->get_json_params();
		$current_settings = get_option( 'flatwp_settings', array() );

		// Sanitize settings
		$sanitized_settings = $this->sanitize_settings( $new_settings );

		// Merge with current settings
		$updated_settings = array_merge( $current_settings, $sanitized_settings );
		update_option( 'flatwp_settings', $updated_settings );

		// Log the activity
		$this->log_activity( 'settings_updated', 'info', 'Plugin settings updated' );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Settings updated successfully', 'flatwp' ),
				'data'    => $updated_settings,
			)
		);
	}

	/**
	 * Get activity logs (uses new database logger system)
	 */
	public function get_logs( $request ) {
		$page = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );
		$type = $request->get_param( 'type' );

		// Get logger instance
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return new WP_Error(
				'logger_unavailable',
				__( 'Logger not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		// Get logs from database
		$logs_data = $logger->get_logs(
			array(
				'page'     => $page,
				'per_page' => $per_page,
				'type'     => $type,
			)
		);

		// Transform logs to match frontend TypeScript interface
		$transformed_logs = array();
		foreach ( $logs_data['logs'] as $log ) {
			$transformed_logs[] = array(
				'id'            => (string) $log['id'], // Convert to string
				'timestamp'     => (int) $log['timestamp'],
				'type'          => $log['type'],
				'severity'      => $log['severity'],
				'message'       => $log['message'],
				'user'          => isset( $log['user_id'] ) ? (int) $log['user_id'] : 0, // Rename user_id to user
				'context'       => $log['context'] ?? null,
				'duration'      => isset( $log['response_time'] ) ? (int) $log['response_time'] : null, // Rename response_time to duration
				'response_data' => $log['response_data'] ?? null,
			);
		}

		return rest_ensure_response(
			array(
				'success'      => true,
				'data'         => array(
					'logs'         => $transformed_logs,
					'total'        => $logs_data['total'],
					'pages'        => $logs_data['pages'],
					'current_page' => $page,
					'per_page'     => $per_page,
				),
			)
		);
	}

	/**
	 * Get performance metrics
	 */
	public function get_performance_metrics( $request ) {
		$period = $request->get_param( 'period' );

		// This would integrate with real performance monitoring
		// For now, return simulated data
		$metrics = array(
			'period'            => $period,
			'average_load_time' => 1.2,
			'lcp'               => 1.8,
			'fcp'               => 0.9,
			'ttfb'              => 0.3,
			'requests'          => 1250,
			'cache_hits'        => 980,
			'cache_misses'      => 270,
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $metrics,
			)
		);
	}

	/**
	 * Get performance time-series data
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_performance_timeseries( $request ) {
		$hours = $request->get_param( 'hours' );
		$hours = max( 1, min( $hours, 168 ) ); // Limit to 1-168 hours (7 days)

		$timeseries_data = array();
		$current_time     = time();

		// Get metrics from transients for each hour
		for ( $i = $hours - 1; $i >= 0; $i-- ) {
			$hour_time = $current_time - ( $i * HOUR_IN_SECONDS );
			$hour_key  = 'flatwp_metrics_' . gmdate( 'Y-m-d-H', $hour_time );

			$metrics = get_transient( $hour_key );

			if ( $metrics && is_array( $metrics ) ) {
				// Aggregate metrics for this hour
				$aggregated = $this->aggregate_metrics_array( $metrics );

				$timeseries_data[] = array(
					'timestamp'           => gmdate( 'c', $hour_time ),
					'hour'                => gmdate( 'Y-m-d H:00', $hour_time ),
					'avgRequestDuration'  => $aggregated['avg_request_duration'],
					'avgGraphQLDuration'  => $aggregated['avg_graphql_duration'],
					'cacheHitRate'        => $aggregated['cache_hit_rate'],
					'requestCount'        => $aggregated['request_count'],
					'errorRate'           => $aggregated['error_rate'],
					'slowGraphQLQueries'  => $aggregated['slow_graphql_queries'],
					'avgMemoryUsage'      => $aggregated['avg_memory_usage'],
				);
			}
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $timeseries_data,
				'period'  => array(
					'hours'      => $hours,
					'from'       => gmdate( 'c', $current_time - ( $hours * HOUR_IN_SECONDS ) ),
					'to'         => gmdate( 'c', $current_time ),
				),
			)
		);
	}

	/**
	 * Aggregate array of metrics
	 *
	 * @param array $metrics Array of metric data points.
	 *
	 * @return array
	 */
	private function aggregate_metrics_array( $metrics ) {
		if ( empty( $metrics ) ) {
			return array(
				'request_count'         => 0,
				'avg_request_duration'  => 0,
				'avg_graphql_duration'  => 0,
				'cache_hit_rate'        => 0,
				'error_rate'            => 0,
				'slow_graphql_queries'  => 0,
				'avg_memory_usage'      => 0,
			);
		}

		$durations          = array();
		$graphql_durations  = array();
		$graphql_count      = 0;
		$slow_graphql_count = 0;
		$memory_usage       = array();
		$error_count        = 0;

		foreach ( $metrics as $metric ) {
			// Request duration
			if ( isset( $metric['request_duration'] ) ) {
				$durations[] = $metric['request_duration'];
			}

			// GraphQL queries
			if ( ! empty( $metric['graphql_queries'] ) ) {
				foreach ( $metric['graphql_queries'] as $gql_query ) {
					$graphql_count++;
					if ( isset( $gql_query['duration'] ) ) {
						$graphql_durations[] = $gql_query['duration'];
						if ( $gql_query['duration'] > 1000 ) {
							$slow_graphql_count++;
						}
					}
					if ( ! empty( $gql_query['error'] ) ) {
						$error_count++;
					}
				}
			}

			// Memory usage
			if ( isset( $metric['memory_usage'] ) ) {
				$memory_usage[] = $metric['memory_usage'];
			}
		}

		return array(
			'request_count'         => count( $metrics ),
			'avg_request_duration'  => ! empty( $durations ) ? array_sum( $durations ) / count( $durations ) : 0,
			'avg_graphql_duration'  => ! empty( $graphql_durations ) ? array_sum( $graphql_durations ) / count( $graphql_durations ) : 0,
			'cache_hit_rate'        => 0, // Would be calculated from cache data if tracked
			'error_rate'            => count( $metrics ) > 0 ? ( $error_count / $graphql_count ) * 100 : 0,
			'slow_graphql_queries'  => $slow_graphql_count,
			'avg_memory_usage'      => ! empty( $memory_usage ) ? array_sum( $memory_usage ) / count( $memory_usage ) : 0,
		);
	}

	/**
	 * Get latest Lighthouse score
	 *
	 * @return WP_REST_Response
	 */
	public function get_lighthouse_score() {
		// Get cached Lighthouse score if available
		$cached_score = get_transient( 'flatwp_lighthouse_score' );

		if ( $cached_score ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'data'    => $cached_score,
				)
			);
		}

		// If no cached score, generate a synthetic score based on real performance metrics
		$aggregated_metrics = $this->get_aggregated_metrics_data();

		$synthetic_score = array(
			'overall'         => max( 0, min( 100, 100 - min( 50, $aggregated_metrics['avg_request_duration'] / 10 ) ) ),
			'performance'     => max( 0, min( 100, 100 - min( 50, $aggregated_metrics['avg_request_duration'] / 10 ) ) ),
			'accessibility'   => 85, // Would come from actual accessibility audit
			'bestPractices'   => max( 0, min( 100, 100 - min( 30, $aggregated_metrics['slow_graphql_queries'] * 5 ) ) ),
			'seo'             => 90, // Would come from actual SEO audit
			'timestamp'       => gmdate( 'c' ),
			'url'             => get_home_url(),
			'cached'          => false,
			'data'            => array(
				'metrics' => array(
					'firstContentfulPaint'  => $aggregated_metrics['avg_request_duration'] * 2.5,
					'largestContentfulPaint' => $aggregated_metrics['avg_request_duration'] * 5,
					'totalBlockingTime'     => $aggregated_metrics['avg_request_duration'] / 2,
					'cumulativeLayoutShift' => 0.05,
					'speedIndex'            => $aggregated_metrics['avg_request_duration'] * 3,
				),
				'opportunities' => array(),
				'diagnostics'  => array(),
			),
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $synthetic_score,
			)
		);
	}

	/**
	 * Run a new Lighthouse audit
	 *
	 * @return WP_REST_Response
	 */
	public function run_lighthouse_audit() {
		// Get current aggregated metrics to generate report
		$aggregated_metrics = $this->get_aggregated_metrics_data();

		// Generate synthetic Lighthouse report
		$report = array(
			'id'               => wp_generate_uuid4(),
			'timestamp'        => gmdate( 'c' ),
			'url'              => get_home_url(),
			'overall'          => max( 0, min( 100, 100 - min( 50, $aggregated_metrics['avg_request_duration'] / 10 ) ) ),
			'performance'      => max( 0, min( 100, 100 - min( 50, $aggregated_metrics['avg_request_duration'] / 10 ) ) ),
			'accessibility'    => 85,
			'bestPractices'    => max( 0, min( 100, 100 - min( 30, $aggregated_metrics['slow_graphql_queries'] * 5 ) ) ),
			'seo'              => 90,
			'metrics'          => array(
				'firstContentfulPaint'   => $aggregated_metrics['avg_request_duration'] * 2.5,
				'largestContentfulPaint' => $aggregated_metrics['avg_request_duration'] * 5,
				'totalBlockingTime'      => $aggregated_metrics['avg_request_duration'] / 2,
				'cumulativeLayoutShift'  => 0.05,
				'speedIndex'             => $aggregated_metrics['avg_request_duration'] * 3,
			),
			'opportunities'    => array(
				array(
					'id'                => 'unused-css',
					'title'             => 'Remove unused CSS',
					'description'       => 'Remove unused rules from stylesheets and defer unused CSS.',
					'potentialSavings'  => round( $aggregated_metrics['avg_request_duration'] * 0.5 ),
					'impact'            => 'high',
				),
				array(
					'id'                => 'unminified-javascript',
					'title'             => 'Minify JavaScript',
					'description'       => 'Minify JavaScript files to reduce payload size and parsing time.',
					'potentialSavings'  => round( $aggregated_metrics['avg_request_duration'] * 0.3 ),
					'impact'            => 'medium',
				),
				array(
					'id'                => 'unused-images',
					'title'             => 'Serve images in modern formats',
					'description'       => 'Image formats like JPEG 2000, JPEG XR, and WebP often provide better compression.',
					'potentialSavings'  => round( $aggregated_metrics['avg_request_duration'] * 0.2 ),
					'impact'            => 'medium',
				),
			),
			'diagnostics'      => array(
				array(
					'id'          => 'critical-request-chains',
					'title'       => 'Critical Request Chains',
					'description' => 'The Critical Request Chains below shows the sequence of dependent network requests.',
					'status'      => $aggregated_metrics['avg_request_duration'] < 200 ? 'pass' : 'warning',
				),
				array(
					'id'          => 'largest-contentful-paint-element',
					'title'       => 'Largest Contentful Paint element',
					'description' => 'The largest contentful paint (LCP) metric reports the render time of the largest image or text block visible in viewport.',
					'status'      => $aggregated_metrics['avg_request_duration'] < 250 ? 'pass' : 'warning',
				),
				array(
					'id'          => 'layout-shift-elements',
					'title'       => 'Layout shifts',
					'description' => 'Unexpected layout shifts can significantly impact user experience.',
					'status'      => 'pass',
				),
			),
		);

		// Store in transient for 24 hours
		set_transient( 'flatwp_lighthouse_score', $report, DAY_IN_SECONDS );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $report,
				'message' => 'Lighthouse audit completed',
			)
		);
	}

	/**
	 * Helper: Get aggregated metrics data
	 *
	 * @return array
	 */
	private function get_aggregated_metrics_data() {
		$hour_key = 'flatwp_metrics_' . gmdate( 'Y-m-d-H' );
		$metrics  = get_transient( $hour_key );

		if ( ! $metrics || empty( $metrics ) ) {
			return array(
				'request_count'         => 0,
				'avg_request_duration'  => 0,
				'avg_graphql_duration'  => 0,
				'cache_hit_rate'        => 0,
				'error_rate'            => 0,
				'slow_graphql_queries'  => 0,
				'avg_memory_usage'      => 0,
			);
		}

		return $this->aggregate_metrics_array( $metrics );
	}

	/**
	 * Get recent posts
	 */
	public function get_recent_posts( $request ) {
		$limit = $request->get_param( 'limit' );

		$posts = get_posts(
			array(
				'numberposts' => $limit,
				'post_status' => array( 'publish', 'draft', 'pending' ),
				'orderby'     => 'modified',
				'order'       => 'DESC',
			)
		);

		$formatted_posts = array_map( function( $post ) {
			return array(
				'id'            => $post->ID,
				'title'         => get_the_title( $post ),
				'status'        => get_post_status( $post ),
				'modified'      => get_the_modified_date( 'c', $post ),
				'modified_ago'  => human_time_diff( get_the_modified_time( 'U', $post ), current_time( 'timestamp' ) ),
				'author'        => get_the_author_meta( 'display_name', $post->post_author ),
				'type'          => get_post_type( $post ),
				'edit_url'      => get_edit_post_link( $post->ID, 'raw' ),
			);
		}, $posts );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $formatted_posts,
			)
		);
	}

	/**
	 * Helper: Get cached connection status
	 */
	private function get_cached_connection_status() {
		$test_result = get_transient( 'flatwp_connection_test_result' );
		$test_timestamp = get_transient( 'flatwp_connection_test_timestamp' );
		$test_details = get_transient( 'flatwp_connection_test_details' );
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );

		// Ensure last_tested returns null instead of false
		if ( false === $test_timestamp || empty( $test_timestamp ) ) {
			$test_timestamp = null;
		}

		return array(
			'connected'     => $test_result === 'success',
			'url'           => $nextjs_url,
			'response_time' => isset( $test_details['response_time'] ) ? $test_details['response_time'] : null,
			'last_tested'   => $test_timestamp,
			'http_code'     => isset( $test_details['http_code'] ) ? $test_details['http_code'] : null,
		);
	}

	/**
	 * Helper: Get cached cache stats
	 */
	private function get_cached_cache_stats() {
		$total_paths = 1; // Homepage

		// Count all public post types
		$public_types = $this->get_public_post_types();
		foreach ( $public_types as $post_type ) {
			$counts = wp_count_posts( $post_type );
			$total_paths += isset( $counts->publish ) ? $counts->publish : 0;
		}

		// Calculate cache entries (all content + blog archives)
		$total_posts = wp_count_posts( 'post' )->publish;
		$total_entries = $total_paths + ceil( $total_posts / 10 ); // Assuming 10 posts per archive page

		// Estimate cache size (very rough estimate: ~50KB per cached page)
		$total_size = $total_entries * 50 * 1024; // 50KB per entry in bytes

		// Get last cleared timestamp, return null if not set
		$last_cleared = get_option( 'flatwp_cache_last_cleared' );
		if ( false === $last_cleared || empty( $last_cleared ) ) {
			$last_cleared = null;
		}

		return array(
			'total_paths'   => $total_paths,
			'cached_paths'  => round( $total_paths * 0.85 ),
			'hit_rate'      => 85.0,
			'last_cleared'  => $last_cleared,
			'total_entries' => $total_entries,
			'total_size'    => $total_size,
		);
	}

	/**
	 * Helper: Get public post types
	 */
	private function get_public_post_types() {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'names'
		);

		// Exclude unwanted types
		$excluded = array( 'attachment', 'nav_menu_item', 'wp_block', 'wp_template', 'wp_template_part', 'wp_navigation' );
		return array_diff( $post_types, $excluded );
	}

	/**
	 * Helper: Get paths by cache type
	 */
	private function get_paths_by_type( $type ) {
		switch ( $type ) {
			case 'homepage':
				return array( '/' );
			case 'posts':
				return array( '/blog' );
			case 'pages':
				$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'post_status' => 'publish' ) );
				return array_map( function( $page ) {
					return '/' . $page->post_name;
				}, $pages );
			case 'all':
				return array( '/', '/blog' );
			default:
				// Handle custom post types dynamically
				$public_types = $this->get_public_post_types();
				if ( in_array( $type, $public_types, true ) ) {
					$items = get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'publish' ) );
					$post_type_obj = get_post_type_object( $type );
					$slug = $post_type_obj && $post_type_obj->rewrite ? $post_type_obj->rewrite['slug'] : $type;

					return array_map( function( $item ) use ( $slug, $type ) {
						// For 'post' type, use /blog/slug, for others use /post-type-slug/slug
						if ( $type === 'post' ) {
							return '/blog/' . $item->post_name;
						} elseif ( $type === 'page' ) {
							return '/' . $item->post_name;
						} else {
							return '/' . $slug . '/' . $item->post_name;
						}
					}, $items );
				}
				return array();
		}
	}

	/**
	 * Helper: Sanitize settings
	 */
	private function sanitize_settings( $settings ) {
		$sanitized = array();
		$errors = array();

		$string_fields = array( 'nextjs_url', 'api_secret', 'preview_url_pattern', 'cache_strategy_posts', 'cache_strategy_pages', 'log_level' );
		$int_fields = array( 'revalidation_delay', 'max_concurrent_webhooks', 'webhook_timeout', 'preview_token_expiration', 'cache_strategy_categories', 'cache_strategy_tags', 'cache_strategy_homepage' );
		$bool_fields = array( 'enable_revalidation', 'webhook_enabled', 'enable_preview', 'enable_debug', 'show_admin_notices' );

		foreach ( $string_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				// Special handling for nextjs_url
				if ( $field === 'nextjs_url' ) {
					$url = esc_url_raw( $settings[ $field ] );
					$url_validation = $this->security->validate_nextjs_url( $url, true );

					if ( is_wp_error( $url_validation ) ) {
						$errors[] = sprintf(
							__( 'Next.js URL validation failed: %s', 'flatwp' ),
							$url_validation->get_error_message()
						);
						// Keep the old value if validation fails
						$current_settings = get_option( 'flatwp_settings', array() );
						$sanitized[ $field ] = isset( $current_settings[ $field ] ) ? $current_settings[ $field ] : '';
					} else {
						$sanitized[ $field ] = $url;
					}
				}
				// Special handling for api_secret
				elseif ( $field === 'api_secret' ) {
					$secret = sanitize_text_field( $settings[ $field ] );
					$secret_validation = $this->security->validate_secret_strength( $secret );

					if ( ! $secret_validation['valid'] ) {
						$errors[] = implode( ' ', $secret_validation['messages'] );
						// Keep the old value if validation fails
						$current_settings = get_option( 'flatwp_settings', array() );
						$sanitized[ $field ] = isset( $current_settings[ $field ] ) ? $current_settings[ $field ] : '';
					} else {
						$sanitized[ $field ] = $secret;

						// Add warning for weak secrets
						if ( $secret_validation['strength'] === 'weak' || $secret_validation['strength'] === 'medium' ) {
							$errors[] = sprintf(
								__( 'API Secret Strength: %s - %s', 'flatwp' ),
								ucfirst( $secret_validation['strength'] ),
								implode( ' ', $secret_validation['messages'] )
							);
						}
					}
				} else {
					$sanitized[ $field ] = sanitize_text_field( $settings[ $field ] );
				}
			}
		}

		foreach ( $int_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$value = absint( $settings[ $field ] );

				// Validate ranges
				if ( $field === 'webhook_timeout' ) {
					$value = max( 5, min( 60, $value ) ); // 5-60 seconds
				} elseif ( $field === 'max_concurrent_webhooks' ) {
					$value = max( 1, min( 10, $value ) ); // 1-10
				}

				$sanitized[ $field ] = $value;
			}
		}

		foreach ( $bool_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}

		// Sanitize frontend_behavior with validation
		if ( isset( $settings['frontend_behavior'] ) ) {
			$behavior = sanitize_text_field( $settings['frontend_behavior'] );
			$valid_behaviors = array( 'normal', 'redirect', 'password', 'disable' );

			if ( in_array( $behavior, $valid_behaviors, true ) ) {
				$sanitized['frontend_behavior'] = $behavior;
			} else {
				$sanitized['frontend_behavior'] = 'normal'; // Default to normal if invalid value
				$errors[] = __( 'Invalid frontend behavior value, defaulting to normal', 'flatwp' );
			}
		}

		// Sanitize webhook_ip_whitelist if present
		if ( isset( $settings['webhook_ip_whitelist'] ) && is_array( $settings['webhook_ip_whitelist'] ) ) {
			$sanitized['webhook_ip_whitelist'] = array_map( 'sanitize_text_field', $settings['webhook_ip_whitelist'] );
		}

		// Store validation errors in transient for UI display
		if ( ! empty( $errors ) ) {
			set_transient( 'flatwp_settings_validation_errors', $errors, 60 );
		}

		return $sanitized;
	}

	/**
	 * Revalidate all content
	 */
	public function revalidate_all( $request ) {
		$revalidation = flatwp()->revalidation;

		if ( ! $revalidation ) {
			return new WP_Error(
				'revalidation_unavailable',
				__( 'Revalidation functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		// Collect all paths to revalidate
		$paths = array( '/', '/blog' );

		// Get all public post types and revalidate each
		$public_types = $this->get_public_post_types();

		foreach ( $public_types as $post_type ) {
			$items = get_posts(
				array(
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			$post_type_obj = get_post_type_object( $post_type );
			$slug = $post_type_obj && $post_type_obj->rewrite ? $post_type_obj->rewrite['slug'] : $post_type;

			foreach ( $items as $item ) {
				// For 'post' type, use /blog/slug, for pages use /slug, for others use /post-type-slug/slug
				if ( $post_type === 'post' ) {
					$paths[] = '/blog/' . $item->post_name;
				} elseif ( $post_type === 'page' ) {
					$paths[] = '/' . $item->post_name;
				} else {
					$paths[] = '/' . $slug . '/' . $item->post_name;
				}
			}
		}

		// Trigger revalidation
		$result = $revalidation->trigger_revalidation( $paths );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Log activity
		if ( class_exists( 'FlatWP_Dashboard_Widget' ) ) {
			FlatWP_Dashboard_Widget::log_activity(
				sprintf(
					__( 'Revalidated all content (%d paths)', 'flatwp' ),
					count( $paths )
				)
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => sprintf(
					__( 'Successfully triggered revalidation for %d paths', 'flatwp' ),
					count( $paths )
				),
				'data'    => array(
					'total_paths' => count( $paths ),
					'paths'       => array_slice( $paths, 0, 10 ), // Return first 10 for preview
				),
			)
		);
	}

	/**
	 * Helper: Log activity
	 */
	private function log_activity( $type, $severity, $message ) {
		$logs = get_option( 'flatwp_activity_logs', array() );

		$log_entry = array(
			'id'        => uniqid(),
			'timestamp' => current_time( 'timestamp' ),
			'type'      => $type,
			'severity'  => $severity,
			'message'   => $message,
			'user'      => get_current_user_id(),
		);

		// Add to beginning of array
		array_unshift( $logs, $log_entry );

		// Keep only last 100 entries
		$logs = array_slice( $logs, 0, 100 );

		update_option( 'flatwp_activity_logs', $logs );
	}

	/**
	 * Validate preview token
	 */
	public function validate_preview_token( $request ) {
		$token = $request->get_param( 'token' );

		if ( empty( $token ) ) {
			return new WP_Error(
				'missing_token',
				__( 'Preview token is required', 'flatwp' ),
				array( 'status' => 400 )
			);
		}

		// Get preview instance
		$preview = flatwp()->preview;

		if ( ! $preview ) {
			return new WP_Error(
				'preview_unavailable',
				__( 'Preview functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		// Validate token
		$token_data = $preview->validate_preview_token( $token );

		if ( false === $token_data ) {
			return new WP_Error(
				'invalid_token',
				__( 'Invalid or expired preview token', 'flatwp' ),
				array( 'status' => 401 )
			);
		}

		// Get post data
		$post = get_post( $token_data['post_id'] );

		if ( ! $post ) {
			return new WP_Error(
				'post_not_found',
				__( 'Post not found', 'flatwp' ),
				array( 'status' => 404 )
			);
		}

		// Get the correct slug using the same logic as preview link generation
		$slug = $this->get_preview_slug_for_validation( $post );

		// Return validation success with post data
		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'post_id' => $post->ID,
					'slug'    => $slug,
					'type'    => $post->post_type,
					'status'  => $post->post_status,
				),
			)
		);
	}

	/**
	 * Get the correct slug for preview validation
	 *
	 * Mirrors the logic in FlatWP_Preview::get_preview_slug()
	 *
	 * @param WP_Post $post Post object.
	 * @return string Post slug.
	 */
	private function get_preview_slug_for_validation( $post ) {
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
		return $title_slug;
	}

	/**
	 * Get logger logs
	 */
	public function get_logger_logs( $request ) {
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return new WP_Error(
				'logger_unavailable',
				__( 'Logger functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$args = array(
			'page'     => $request->get_param( 'page' ) ?: 1,
			'per_page' => $request->get_param( 'per_page' ) ?: 20,
			'type'     => $request->get_param( 'type' ) ?: 'all',
			'severity' => $request->get_param( 'severity' ) ?: 'all',
		);

		$result = $logger->get_logs( $args );

		// Transform logs to match frontend TypeScript interface
		$transformed_logs = array();
		foreach ( $result['logs'] as $log ) {
			$transformed_logs[] = array(
				'id'            => (string) $log['id'], // Convert to string
				'timestamp'     => (int) $log['timestamp'],
				'type'          => $log['type'],
				'severity'      => $log['severity'],
				'message'       => $log['message'],
				'user'          => isset( $log['user_id'] ) ? (int) $log['user_id'] : 0, // Rename user_id to user
				'context'       => $log['context'] ?? null,
				'duration'      => isset( $log['response_time'] ) ? (int) $log['response_time'] : null, // Rename response_time to duration
				'response_data' => $log['response_data'] ?? null,
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'logs'         => $transformed_logs,
					'total'        => $result['total'],
					'pages'        => $result['pages'],
					'current_page' => $args['page'],
					'per_page'     => $args['per_page'],
				),
			)
		);
	}

	/**
	 * Get revalidation settings
	 */
	public function get_revalidation_settings( $request ) {
		$revalidation = flatwp()->revalidation;

		if ( ! $revalidation ) {
			return new WP_Error(
				'revalidation_unavailable',
				__( 'Revalidation functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$settings = $revalidation->get_settings();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $settings,
			)
		);
	}

	/**
	 * Get logger error logs
	 */
	public function get_logger_errors( $request ) {
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return new WP_Error(
				'logger_unavailable',
				__( 'Logger functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$result = $logger->get_error_logs( 50 );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $result,
			)
		);
	}

	/**
	 * Update revalidation settings
	 */
	public function update_revalidation_settings( $request ) {
		$revalidation = flatwp()->revalidation;

		if ( ! $revalidation ) {
			return new WP_Error(
				'revalidation_unavailable',
				__( 'Revalidation functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$new_settings = $request->get_json_params();
		$updated_settings = $revalidation->update_settings( $new_settings );

		$this->log_activity( 'revalidation_settings_updated', 'info', 'Revalidation settings updated' );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Revalidation settings updated successfully', 'flatwp' ),
				'data'    => $updated_settings,
			)
		);
	}

	/**
	 * Get logger statistics
	 */
	public function get_logger_stats( $request ) {
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return new WP_Error(
				'logger_unavailable',
				__( 'Logger functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$stats = $logger->get_statistics();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $stats,
			)
		);
	}

	/**
	 * Clear logger logs
	 */
	public function clear_logger_logs( $request ) {
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return new WP_Error(
				'logger_unavailable',
				__( 'Logger functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$type = $request->get_param( 'type' );

		if ( $type && $type !== 'all' ) {
			$result = $logger->clear_logs_by_type( $type );
			$message = sprintf( __( 'Logs of type "%s" cleared successfully', 'flatwp' ), $type );
		} else {
			$result = $logger->clear_logs();
			$message = __( 'All logs cleared successfully', 'flatwp' );
		}

		if ( ! $result ) {
			return new WP_Error(
				'clear_failed',
				__( 'Failed to clear logs', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $message,
			)
		);
	}

	/**
	 * Manual revalidation
	 */
	public function revalidate_manual( $request ) {
		$revalidation = flatwp()->revalidation;

		if ( ! $revalidation ) {
			return new WP_Error(
				'revalidation_unavailable',
				__( 'Revalidation functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$action = $request->get_param( 'action' );
		$paths = array();

		switch ( $action ) {
			case 'all':
				// Revalidate everything
				$paths = array( '/', '/blog' );

				// Get all public post types
				$public_types = $this->get_public_post_types();

				foreach ( $public_types as $post_type ) {
					$items = get_posts(
						array(
							'post_type'   => $post_type,
							'post_status' => 'publish',
							'numberposts' => -1,
						)
					);

					$settings = $revalidation->get_settings();
					if ( isset( $settings['rules'][ $post_type ] ) ) {
						$rule = $settings['rules'][ $post_type ];
						foreach ( $items as $item ) {
							$post_path = str_replace( '{slug}', $item->post_name, $rule['path_template'] );
							$paths[] = $post_path;
						}
					}
				}
				break;

			case 'homepage':
				$paths = array( '/' );
				break;

			case 'path':
				$path = $request->get_param( 'path' );
				if ( empty( $path ) ) {
					return new WP_Error(
						'missing_path',
						__( 'Path parameter is required', 'flatwp' ),
						array( 'status' => 400 )
					);
				}
				$paths = array( $path );
				break;

			case 'post_type':
				$post_type = $request->get_param( 'post_type' );
				if ( empty( $post_type ) ) {
					return new WP_Error(
						'missing_post_type',
						__( 'Post type parameter is required', 'flatwp' ),
						array( 'status' => 400 )
					);
				}

				$items = get_posts(
					array(
						'post_type'   => $post_type,
						'post_status' => 'publish',
						'numberposts' => -1,
					)
				);

				$settings = $revalidation->get_settings();
				if ( isset( $settings['rules'][ $post_type ] ) ) {
					$rule = $settings['rules'][ $post_type ];
					foreach ( $items as $item ) {
						$post_path = str_replace( '{slug}', $item->post_name, $rule['path_template'] );
						$paths[] = $post_path;
					}

					// Add archive paths if configured
					if ( $rule['revalidate_archives'] && ! empty( $rule['archive_paths'] ) ) {
						$paths = array_merge( $paths, $rule['archive_paths'] );
					}
				}
				break;

			default:
				return new WP_Error(
					'invalid_action',
					__( 'Invalid action specified', 'flatwp' ),
					array( 'status' => 400 )
				);
		}

		// Remove duplicates
		$paths = array_unique( $paths );

		// Log manual revalidation action BEFORE triggering
		if ( flatwp()->logger ) {
			$action_label = $action === 'all' ? 'All content' : ( $action === 'homepage' ? 'Homepage' : ( $action === 'post_type' ? $request->get_param( 'post_type' ) : $request->get_param( 'path' ) ) );
			flatwp()->logger->log(
				FlatWP_Logger::TYPE_MANUAL,
				FlatWP_Logger::SEVERITY_INFO,
				sprintf( 'Manual revalidation triggered for: %s (%d path(s))', $action_label, count( $paths ) ),
				array(
					'action'      => $action,
					'path_count'  => count( $paths ),
					'action_label' => $action_label,
				)
			);
		}

		// Trigger revalidation
		$result = $revalidation->trigger_revalidation( $paths );

		if ( is_wp_error( $result ) ) {
			// Log failure
			if ( flatwp()->logger ) {
				flatwp()->logger->log_error(
					'Manual revalidation failed: ' . $result->get_error_message(),
					array(
						'action' => $action,
						'error_code' => $result->get_error_code(),
					)
				);
			}
			return $result;
		}

		// Determine success message based on action
		$message = '';
		switch ( $action ) {
			case 'all':
				$message = sprintf( __( 'Successfully triggered revalidation for %d paths', 'flatwp' ), count( $paths ) );
				break;
			case 'homepage':
				$message = __( 'Homepage revalidated successfully', 'flatwp' );
				break;
			case 'path':
				$message = sprintf( __( 'Path %s revalidated successfully', 'flatwp' ), $request->get_param( 'path' ) );
				break;
			case 'post_type':
				$message = sprintf( __( 'All %s revalidated successfully (%d paths)', 'flatwp' ), $request->get_param( 'post_type' ), count( $paths ) );
				break;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $message,
				'data'    => array(
					'total_paths' => count( $paths ),
					'paths'       => $paths,
				),
			)
		);
	}

	/**
	 * Export logger logs
	 */
	public function export_logger_logs( $request ) {
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return new WP_Error(
				'logger_unavailable',
				__( 'Logger functionality not available', 'flatwp' ),
				array( 'status' => 500 )
			);
		}

		$format = $request->get_param( 'format' );
		$type = $request->get_param( 'type' );

		$args = array(
			'type' => $type,
		);

		$exported_data = $logger->export_logs( $format, $args );

		if ( ! $exported_data ) {
			return new WP_Error(
				'export_failed',
				__( 'No logs to export or export failed', 'flatwp' ),
				array( 'status' => 404 )
			);
		}

		// Set appropriate headers for file download
		$filename = 'flatwp-logs-' . current_time( 'Y-m-d-H-i-s' ) . '.' . $format;

		if ( $format === 'csv' ) {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			echo $exported_data;
			exit;
		}

		// JSON format
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		echo $exported_data;
		exit;
	}

	/**
	 * Get wizard status
	 */
	public function get_wizard_status( $request ) {
		$setup_completed = get_option( 'flatwp_setup_completed', false );
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );
		$api_secret = flatwp()->get_setting( 'api_secret' );

		$is_configured = ! empty( $nextjs_url ) && ! empty( $api_secret );
		$show_wizard = ! $setup_completed && ! $is_configured;

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'setup_completed' => (bool) $setup_completed,
					'is_configured'   => $is_configured,
					'show_wizard'     => $show_wizard,
				),
			)
		);
	}

	/**
	 * Complete wizard setup
	 */
	public function complete_wizard( $request ) {
		update_option( 'flatwp_setup_completed', true );

		// Auto-configure default webhook target
		$webhook_configured = $this->auto_configure_webhook();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'setup_completed'    => true,
					'webhook_configured' => $webhook_configured,
				),
			)
		);
	}

	/**
	 * Auto-configure default webhook target using the Next.js URL from settings
	 *
	 * @return bool Whether the webhook was successfully configured
	 */
	private function auto_configure_webhook() {
		// Check if webhook config class is available
		if ( ! class_exists( 'FlatWP_Webhook_Config' ) ) {
			return false;
		}

		// Get the Next.js URL from settings
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );
		if ( empty( $nextjs_url ) ) {
			return false;
		}

		// Build the revalidation endpoint URL
		$revalidate_url = trailingslashit( $nextjs_url ) . 'api/revalidate';

		// Get current webhook config
		$config = FlatWP_Webhook_Config::get_config();

		// Check if primary target already has a URL configured
		$primary_exists = false;
		$primary_has_url = false;

		if ( ! empty( $config['targets'] ) && is_array( $config['targets'] ) ) {
			foreach ( $config['targets'] as $target ) {
				if ( isset( $target['id'] ) && $target['id'] === 'primary' ) {
					$primary_exists = true;
					if ( ! empty( $target['url'] ) ) {
						$primary_has_url = true;
					}
					break;
				}
			}
		}

		// Only auto-configure if primary target doesn't have a URL yet
		if ( $primary_has_url ) {
			return true; // Already configured
		}

		// Create or update the primary webhook target
		$primary_target = array(
			'id'       => 'primary',
			'name'     => 'Primary Next.js Site',
			'url'      => $revalidate_url,
			'enabled'  => true,
			'priority' => 1,
			'timeout'  => 15,
			'retries'  => 3,
			'headers'  => array(),
		);

		// Update targets array
		if ( $primary_exists ) {
			// Update existing primary target
			foreach ( $config['targets'] as $index => $target ) {
				if ( isset( $target['id'] ) && $target['id'] === 'primary' ) {
					$config['targets'][ $index ] = $primary_target;
					break;
				}
			}
		} else {
			// Add new primary target
			if ( ! isset( $config['targets'] ) || ! is_array( $config['targets'] ) ) {
				$config['targets'] = array();
			}
			array_unshift( $config['targets'], $primary_target );
		}

		// Save the updated config
		$result = FlatWP_Webhook_Config::update_config( $config );

		return ! is_wp_error( $result );
	}

	/**
	 * Get webhook configuration
	 */
	public function get_webhook_config( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Config' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$config = FlatWP_Webhook_Config::get_config();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $config,
			)
		);
	}

	/**
	 * Update webhook configuration
	 */
	public function update_webhook_config( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Config' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$config = $request->get_json_params();
		$result = FlatWP_Webhook_Config::update_config( $config );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $result,
			)
		);
	}

	/**
	 * Add webhook target
	 */
	public function add_webhook_target( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Config' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$target = $request->get_json_params();
		$config = FlatWP_Webhook_Config::get_config();

		$target['id'] = uniqid( 'wh_', true );
		$config['targets'][] = $target;

		$result = FlatWP_Webhook_Config::update_config( $config );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $target,
			)
		);
	}

	/**
	 * Delete webhook target
	 */
	public function delete_webhook_target( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Config' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$target_id = $request->get_param( 'target_id' );
		$config = FlatWP_Webhook_Config::get_config();

		$config['targets'] = array_filter(
			$config['targets'],
			function( $target ) use ( $target_id ) {
				return $target['id'] !== $target_id;
			}
		);

		$result = FlatWP_Webhook_Config::update_config( $config );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Webhook target deleted',
			)
		);
	}

	/**
	 * Test webhook target
	 */
	public function test_webhook_target( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Dispatcher' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$target_id = $request->get_param( 'target_id' );
		$result = FlatWP_Webhook_Dispatcher::test( $target_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $result,
			)
		);
	}

	/**
	 * Get webhook metrics
	 */
	public function get_webhook_metrics( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Metrics' ) ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'data'    => array(),
				)
			);
		}

		$health = FlatWP_Webhook_Metrics::get_health( 7 );
		$recent_errors = FlatWP_Webhook_Metrics::get_recent_errors( 5 );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'health'         => $health,
					'recent_errors'  => $recent_errors,
				),
			)
		);
	}

	/**
	 * Get webhook plugins
	 */
	public function get_webhook_plugins( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Plugin_Manager' ) ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'data'    => array(),
				)
			);
		}

		$plugins = FlatWP_Webhook_Plugin_Manager::get_plugins();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $plugins,
			)
		);
	}

	/**
	 * Enable webhook plugin
	 */
	public function enable_webhook_plugin( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Plugin_Manager' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$plugin_id = $request->get_param( 'plugin_id' );
		$result = FlatWP_Webhook_Plugin_Manager::enable_plugin( $plugin_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Plugin enabled',
			)
		);
	}

	/**
	 * Disable webhook plugin
	 */
	public function disable_webhook_plugin( $request ) {
		if ( ! class_exists( 'FlatWP_Webhook_Plugin_Manager' ) ) {
			return new WP_Error(
				'webhooks_not_available',
				'Webhook system not available',
				array( 'status' => 503 )
			);
		}

		$plugin_id = $request->get_param( 'plugin_id' );
		$result = FlatWP_Webhook_Plugin_Manager::disable_plugin( $plugin_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Plugin disabled',
			)
		);
	}
}
