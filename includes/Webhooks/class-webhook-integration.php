<?php
/**
 * FlatWP Webhook Integration
 *
 * Integrates the webhook system with the main revalidation class.
 * Manages the transition from old direct webhook calls to the new webhook system.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Integration {

	/**
	 * Initialize webhook system
	 */
	public static function init() {
		// Create metrics table if metrics are enabled
		if ( class_exists( 'FlatWP_Webhook_Metrics' ) ) {
			FlatWP_Webhook_Metrics::create_table();
		}

		// Load webhook plugins
		self::load_plugins();

		// Setup scheduled cleanup of old metrics
		if ( ! wp_next_scheduled( 'flatwp_webhook_cleanup_metrics' ) ) {
			wp_schedule_event( time(), 'daily', 'flatwp_webhook_cleanup_metrics' );
		}

		add_action( 'flatwp_webhook_cleanup_metrics', array( __CLASS__, 'cleanup_metrics' ) );

		// Allow themes/plugins to register custom webhook plugins
		do_action( 'flatwp_webhook_system_init' );
	}

	/**
	 * Load built-in webhook plugins
	 */
	private static function load_plugins() {
		$plugins_dir = dirname( __FILE__ ) . '/Plugins';

		// Load Yoast SEO plugin
		if ( file_exists( "$plugins_dir/class-webhook-plugin-yoast-seo.php" ) ) {
			require_once "$plugins_dir/class-webhook-plugin-yoast-seo.php";
		}

		// Load ACF plugin
		if ( file_exists( "$plugins_dir/class-webhook-plugin-acf.php" ) ) {
			require_once "$plugins_dir/class-webhook-plugin-acf.php";
		}

		do_action( 'flatwp_webhook_plugins_loaded' );
	}

	/**
	 * Trigger webhook from revalidation
	 *
	 * This is the bridge between the old FlatWP_Revalidation class
	 * and the new webhook system.
	 */
	public static function trigger_from_revalidation( $paths, $post = null ) {
		// Check if ISR webhook is enabled in the config
		if ( ! FlatWP_Webhook_Config::is_trigger_enabled( 'post' ) ) {
			return;
		}

		$payload = array(
			'paths' => $paths,
		);

		// Add post data if available
		if ( $post ) {
			$payload['post'] = array(
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'type'      => $post->post_type,
				'status'    => $post->post_status,
				'slug'      => $post->post_name,
				'url'       => get_permalink( $post->ID ),
				'modified'  => $post->post_modified,
			);
		}

		// Dispatch webhook
		$result = FlatWP_Webhook_Dispatcher::dispatch( 'post.revalidate', $payload );

		return $result;
	}

	/**
	 * Cleanup old metrics
	 */
	public static function cleanup_metrics() {
		if ( class_exists( 'FlatWP_Webhook_Metrics' ) ) {
			FlatWP_Webhook_Metrics::cleanup_old_metrics( 30 );
		}
	}

	/**
	 * REST API endpoints for webhook management
	 */
	public static function register_rest_endpoints() {
		register_rest_route(
			'flatwp/v1',
			'/webhooks/config',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_get_config' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/webhooks/config',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_update_config' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/webhooks/test/(?P<target_id>\w+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_test_webhook' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/webhooks/metrics',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_get_metrics' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/webhooks/plugins',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_get_plugins' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/webhooks/plugins/(?P<plugin_id>\w+)/enable',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_enable_plugin' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/webhooks/plugins/(?P<plugin_id>\w+)/disable',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_disable_plugin' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
	}

	/**
	 * REST endpoint: Get webhook configuration
	 */
	public static function rest_get_config() {
		return rest_ensure_response( FlatWP_Webhook_Config::get_config() );
	}

	/**
	 * REST endpoint: Update webhook configuration
	 */
	public static function rest_update_config( $request ) {
		$config = $request->get_json_params();

		$result = FlatWP_Webhook_Config::update_config( $config );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response( $result );
	}

	/**
	 * REST endpoint: Test webhook
	 */
	public static function rest_test_webhook( $request ) {
		$target_id = $request->get_param( 'target_id' );

		$result = FlatWP_Webhook_Dispatcher::test( $target_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response( $result );
	}

	/**
	 * REST endpoint: Get webhook metrics
	 */
	public static function rest_get_metrics() {
		if ( ! class_exists( 'FlatWP_Webhook_Metrics' ) ) {
			return rest_ensure_response( array() );
		}

		$health = FlatWP_Webhook_Metrics::get_health( 7 );
		$recent_errors = FlatWP_Webhook_Metrics::get_recent_errors( 5 );

		return rest_ensure_response(
			array(
				'health'         => $health,
				'recent_errors'  => $recent_errors,
			)
		);
	}

	/**
	 * REST endpoint: Get webhook plugins
	 */
	public static function rest_get_plugins() {
		return rest_ensure_response( FlatWP_Webhook_Plugin_Manager::get_plugins() );
	}

	/**
	 * REST endpoint: Enable plugin
	 */
	public static function rest_enable_plugin( $request ) {
		$plugin_id = $request->get_param( 'plugin_id' );

		$result = FlatWP_Webhook_Plugin_Manager::enable_plugin( $plugin_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * REST endpoint: Disable plugin
	 */
	public static function rest_disable_plugin( $request ) {
		$plugin_id = $request->get_param( 'plugin_id' );

		$result = FlatWP_Webhook_Plugin_Manager::disable_plugin( $plugin_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( $result, 400 );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Check admin permissions for REST endpoints
	 */
	public static function check_admin_permissions() {
		return current_user_can( 'manage_options' );
	}
}
