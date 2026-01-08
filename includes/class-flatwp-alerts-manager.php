<?php
/**
 * FlatWP Alerts Manager
 *
 * Manages performance alerts and notifications
 *
 * @package FlatWP
 * @since 0.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FlatWP_Alerts_Manager
 */
class FlatWP_Alerts_Manager {

	/**
	 * Instance of this class
	 *
	 * @var FlatWP_Alerts_Manager
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return FlatWP_Alerts_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// REST API endpoint for alerts
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );

		// Cron job for checking thresholds
		add_action( 'flatwp_check_performance_thresholds', array( $this, 'check_thresholds' ) );
		if ( ! wp_next_scheduled( 'flatwp_check_performance_thresholds' ) ) {
			wp_schedule_event( time(), 'five_minutes', 'flatwp_check_performance_thresholds' );
		}
	}

	/**
	 * Register REST endpoints
	 */
	public function register_endpoints() {
		// Get alerts
		register_rest_route(
			'flatwp/v1',
			'/alerts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_alerts' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Create alert
		register_rest_route(
			'flatwp/v1',
			'/alerts',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_alert' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Update alert
		register_rest_route(
			'flatwp/v1',
			'/alerts/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_alert' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Delete alert
		register_rest_route(
			'flatwp/v1',
			'/alerts/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_alert' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Get active alerts
		register_rest_route(
			'flatwp/v1',
			'/alerts/active',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_active_alerts' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Get alerts
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_alerts( $request ) {
		$alerts = get_option( 'flatwp_performance_alerts', array() );

		if ( empty( $alerts ) ) {
			$alerts = $this->get_default_alerts();
			update_option( 'flatwp_performance_alerts', $alerts );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $alerts,
			)
		);
	}

	/**
	 * Create alert
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function create_alert( $request ) {
		$params = $request->get_json_params();

		$alert = array(
			'id'         => time(), // Simple unique ID
			'name'       => sanitize_text_field( $params['name'] ?? '' ),
			'metric'     => sanitize_text_field( $params['metric'] ?? '' ),
			'condition'  => sanitize_text_field( $params['condition'] ?? 'greater_than' ),
			'threshold'  => floatval( $params['threshold'] ?? 0 ),
			'duration'   => intval( $params['duration'] ?? 300 ), // seconds
			'enabled'    => boolval( $params['enabled'] ?? true ),
			'severity'   => sanitize_text_field( $params['severity'] ?? 'warning' ),
			'webhook_url' => sanitize_url( $params['webhook_url'] ?? '' ),
			'email'      => sanitize_email( $params['email'] ?? get_option( 'admin_email' ) ),
			'created_at' => current_time( 'mysql' ),
		);

		if ( empty( $alert['name'] ) || empty( $alert['metric'] ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'message' => 'Name and metric are required',
				),
				400
			);
		}

		$alerts   = get_option( 'flatwp_performance_alerts', array() );
		$alerts[] = $alert;
		update_option( 'flatwp_performance_alerts', $alerts );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $alert,
				'message' => 'Alert created successfully',
			)
		);
	}

	/**
	 * Update alert
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function update_alert( $request ) {
		$alert_id = intval( $request['id'] );
		$params   = $request->get_json_params();

		$alerts = get_option( 'flatwp_performance_alerts', array() );

		foreach ( $alerts as &$alert ) {
			if ( $alert['id'] === $alert_id ) {
				$alert['name']        = sanitize_text_field( $params['name'] ?? $alert['name'] );
				$alert['metric']      = sanitize_text_field( $params['metric'] ?? $alert['metric'] );
				$alert['condition']   = sanitize_text_field( $params['condition'] ?? $alert['condition'] );
				$alert['threshold']   = floatval( $params['threshold'] ?? $alert['threshold'] );
				$alert['duration']    = intval( $params['duration'] ?? $alert['duration'] );
				$alert['enabled']     = boolval( $params['enabled'] ?? $alert['enabled'] );
				$alert['severity']    = sanitize_text_field( $params['severity'] ?? $alert['severity'] );
				$alert['webhook_url'] = sanitize_url( $params['webhook_url'] ?? $alert['webhook_url'] );
				$alert['email']       = sanitize_email( $params['email'] ?? $alert['email'] );
				$alert['updated_at']  = current_time( 'mysql' );
				break;
			}
		}

		update_option( 'flatwp_performance_alerts', $alerts );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $alert,
				'message' => 'Alert updated successfully',
			)
		);
	}

	/**
	 * Delete alert
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete_alert( $request ) {
		$alert_id = intval( $request['id'] );
		$alerts   = get_option( 'flatwp_performance_alerts', array() );

		$alerts = array_filter( $alerts, function( $alert ) use ( $alert_id ) {
			return $alert['id'] !== $alert_id;
		});

		update_option( 'flatwp_performance_alerts', array_values( $alerts ) );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Alert deleted successfully',
			)
		);
	}

	/**
	 * Get active alerts
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_active_alerts( $request ) {
		$active = get_option( 'flatwp_active_alerts', array() );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $active,
			)
		);
	}

	/**
	 * Check performance thresholds
	 */
	public function check_thresholds() {
		$alerts = get_option( 'flatwp_performance_alerts', array() );
		if ( empty( $alerts ) ) {
			return;
		}

		$metrics = get_transient( 'flatwp_metrics_' . gmdate( 'Y-m-d-H' ) );
		if ( empty( $metrics ) ) {
			return;
		}

		$aggregated = $this->aggregate_metrics( $metrics );
		$active_alerts = array();

		foreach ( $alerts as $alert ) {
			if ( ! $alert['enabled'] ) {
				continue;
			}

			$value = $aggregated[ $alert['metric'] ] ?? null;
			if ( $value === null ) {
				continue;
			}

			if ( $this->check_threshold( $value, $alert['condition'], $alert['threshold'] ) ) {
				$active_alerts[] = array(
					'alert_id'   => $alert['id'],
					'name'       => $alert['name'],
					'metric'     => $alert['metric'],
					'value'      => $value,
					'threshold'  => $alert['threshold'],
					'severity'   => $alert['severity'],
					'triggered_at' => current_time( 'mysql' ),
				);

				// Send notification
				$this->send_notification( $alert, $value );
			}
		}

		update_option( 'flatwp_active_alerts', $active_alerts );
	}

	/**
	 * Check if value meets threshold condition
	 *
	 * @param float  $value Current value.
	 * @param string $condition Condition operator.
	 * @param float  $threshold Threshold value.
	 *
	 * @return bool
	 */
	private function check_threshold( $value, $condition, $threshold ) {
		switch ( $condition ) {
			case 'greater_than':
				return $value > $threshold;
			case 'less_than':
				return $value < $threshold;
			case 'equals':
				return abs( $value - $threshold ) < 0.01;
			case 'greater_than_or_equal':
				return $value >= $threshold;
			case 'less_than_or_equal':
				return $value <= $threshold;
			default:
				return false;
		}
	}

	/**
	 * Send alert notification
	 *
	 * @param array $alert Alert configuration.
	 * @param float $value Current metric value.
	 */
	private function send_notification( $alert, $value ) {
		$subject = sprintf(
			'[FlatWP Alert] %s - %s is %f',
			$alert['severity'],
			$alert['name'],
			$value
		);

		$message = sprintf(
			"Performance Alert\n\n" .
			"Alert: %s\n" .
			"Metric: %s\n" .
			"Current Value: %f\n" .
			"Threshold: %f\n" .
			"Severity: %s\n" .
			"Time: %s\n\n" .
			"Visit: %s",
			$alert['name'],
			$alert['metric'],
			$value,
			$alert['threshold'],
			$alert['severity'],
			current_time( 'mysql' ),
			admin_url( 'admin.php?page=flatwp&tab=performance' )
		);

		// Send email
		if ( ! empty( $alert['email'] ) ) {
			wp_mail( $alert['email'], $subject, $message );
		}

		// Send webhook
		if ( ! empty( $alert['webhook_url'] ) ) {
			wp_remote_post(
				$alert['webhook_url'],
				array(
					'body'        => wp_json_encode(
						array(
							'alert'     => $alert,
							'value'     => $value,
							'timestamp' => current_time( 'c' ),
						)
					),
					'headers'     => array( 'Content-Type' => 'application/json' ),
					'timeout'     => 5,
					'blocking'    => false,
				)
			);
		}

		// Log alert
		FlatWP_Logger::log(
			sprintf( 'Performance alert triggered: %s (value: %f, threshold: %f)', $alert['name'], $value, $alert['threshold'] ),
			'alert'
		);
	}

	/**
	 * Aggregate metrics
	 *
	 * @param array $metrics Array of metrics.
	 *
	 * @return array
	 */
	private function aggregate_metrics( $metrics ) {
		$aggregated = array(
			'avg_request_duration'   => 0,
			'cache_hit_rate'         => 0,
			'avg_graphql_duration'   => 0,
			'slow_graphql_queries'   => 0,
			'error_rate'             => 0,
			'avg_memory_usage'       => 0,
		);

		if ( empty( $metrics ) ) {
			return $aggregated;
		}

		$durations       = array();
		$graphql_times   = array();
		$slow_gql        = 0;
		$memory_usage    = array();
		$errors          = 0;

		foreach ( $metrics as $metric ) {
			if ( isset( $metric['request_duration'] ) ) {
				$durations[] = $metric['request_duration'];
			}

			if ( ! empty( $metric['graphql_queries'] ) ) {
				foreach ( $metric['graphql_queries'] as $gql ) {
					if ( isset( $gql['duration'] ) ) {
						$graphql_times[] = $gql['duration'];
						if ( $gql['duration'] > 1000 ) {
							$slow_gql++;
						}
					}
				}
			}

			if ( isset( $metric['memory_usage'] ) ) {
				$memory_usage[] = $metric['memory_usage'];
			}

			if ( isset( $metric['rest_requests'] ) ) {
				foreach ( $metric['rest_requests'] as $req ) {
					if ( $req['status'] >= 400 ) {
						$errors++;
					}
				}
			}
		}

		if ( ! empty( $durations ) ) {
			$aggregated['avg_request_duration'] = array_sum( $durations ) / count( $durations );
		}

		if ( ! empty( $graphql_times ) ) {
			$aggregated['avg_graphql_duration'] = array_sum( $graphql_times ) / count( $graphql_times );
			$aggregated['slow_graphql_queries'] = $slow_gql;
		}

		if ( ! empty( $memory_usage ) ) {
			$aggregated['avg_memory_usage'] = array_sum( $memory_usage ) / count( $memory_usage );
		}

		if ( ! empty( $metrics ) ) {
			$aggregated['error_rate'] = ( $errors / count( $metrics ) ) * 100;
		}

		return $aggregated;
	}

	/**
	 * Get default alerts
	 *
	 * @return array
	 */
	private function get_default_alerts() {
		return array(
			array(
				'id'         => time(),
				'name'       => 'High Average Request Duration',
				'metric'     => 'avg_request_duration',
				'condition'  => 'greater_than',
				'threshold'  => 1000, // 1 second in ms
				'duration'   => 300,
				'enabled'    => true,
				'severity'   => 'warning',
				'webhook_url' => '',
				'email'      => get_option( 'admin_email' ),
				'created_at' => current_time( 'mysql' ),
			),
			array(
				'id'         => time() + 1,
				'name'       => 'Low Cache Hit Rate',
				'metric'     => 'cache_hit_rate',
				'condition'  => 'less_than',
				'threshold'  => 70, // 70%
				'duration'   => 300,
				'enabled'    => true,
				'severity'   => 'warning',
				'webhook_url' => '',
				'email'      => get_option( 'admin_email' ),
				'created_at' => current_time( 'mysql' ),
			),
			array(
				'id'         => time() + 2,
				'name'       => 'High Error Rate',
				'metric'     => 'error_rate',
				'condition'  => 'greater_than',
				'threshold'  => 5, // 5%
				'duration'   => 300,
				'enabled'    => true,
				'severity'   => 'error',
				'webhook_url' => '',
				'email'      => get_option( 'admin_email' ),
				'created_at' => current_time( 'mysql' ),
			),
		);
	}

	/**
	 * Check admin permission
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}
}
