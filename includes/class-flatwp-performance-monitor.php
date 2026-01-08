<?php
/**
 * FlatWP Performance Monitoring
 *
 * Tracks application performance metrics across the entire stack
 *
 * @package FlatWP
 * @since 0.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FlatWP_Performance_Monitor
 */
class FlatWP_Performance_Monitor {

	/**
	 * Instance of this class
	 *
	 * @var FlatWP_Performance_Monitor
	 */
	private static $instance = null;

	/**
	 * Performance data for current request
	 *
	 * @var array
	 */
	private $current_metrics = array();

	/**
	 * Start time of the request
	 *
	 * @var float
	 */
	private $request_start_time = 0;

	/**
	 * Get instance
	 *
	 * @return FlatWP_Performance_Monitor
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
		$this->request_start_time = microtime( true );
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// GraphQL hooks
		add_action( 'graphql_execute_query', array( $this, 'track_graphql_query_start' ), 10, 4 );
		add_filter( 'graphql_response', array( $this, 'track_graphql_query_end' ), 10, 2 );

		// REST API hooks
		add_filter( 'rest_dispatch', array( $this, 'track_rest_request' ), 10, 2 );

		// Database hooks
		add_filter( 'query', array( $this, 'track_database_query' ), 10, 1 );

		// Cache hooks
		add_filter( 'pre_get_posts', array( $this, 'track_cache_access' ), 10, 1 );

		// Shutdown to collect metrics
		add_action( 'shutdown', array( $this, 'finalize_metrics' ) );

		// REST API endpoint for metrics
		add_action( 'rest_api_init', array( $this, 'register_metrics_endpoint' ) );
	}

	/**
	 * Track GraphQL query start
	 *
	 * @param string $query Query string.
	 * @param array  $variables Variables.
	 * @param string $operation Operation name.
	 * @param object $schema Schema.
	 */
	public function track_graphql_query_start( $query, $variables, $operation, $schema ) {
		if ( ! isset( $this->current_metrics['graphql_queries'] ) ) {
			$this->current_metrics['graphql_queries'] = array();
		}

		$this->current_metrics['graphql_queries'][] = array(
			'operation'   => $operation,
			'query'       => substr( $query, 0, 500 ), // Truncate for storage
			'start_time'  => microtime( true ),
			'duration'    => 0,
			'error'       => null,
		);
	}

	/**
	 * Track GraphQL query end
	 *
	 * @param array  $response Response.
	 * @param object $result Result.
	 *
	 * @return array
	 */
	public function track_graphql_query_end( $response, $result ) {
		if ( ! empty( $this->current_metrics['graphql_queries'] ) ) {
			$last_query = &$this->current_metrics['graphql_queries'][ count( $this->current_metrics['graphql_queries'] ) - 1 ];
			$last_query['duration'] = ( microtime( true ) - $last_query['start_time'] ) * 1000; // Convert to ms

			if ( ! empty( $result->errors ) ) {
				$last_query['error'] = count( $result->errors ) . ' errors';
			}
		}

		return $response;
	}

	/**
	 * Track REST request
	 *
	 * @param WP_REST_Response|bool $response Response.
	 * @param WP_REST_Server        $server Server.
	 *
	 * @return WP_REST_Response|bool
	 */
	public function track_rest_request( $response, $server ) {
		if ( ! isset( $this->current_metrics['rest_requests'] ) ) {
			$this->current_metrics['rest_requests'] = array();
		}

		$this->current_metrics['rest_requests'][] = array(
			'route'     => isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
			'method'    => isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '',
			'status'    => $response instanceof WP_REST_Response ? $response->get_status() : 200,
			'timestamp' => time(),
		);

		return $response;
	}

	/**
	 * Track database query
	 *
	 * @param string $query Query string.
	 *
	 * @return string
	 */
	public function track_database_query( $query ) {
		if ( ! isset( $this->current_metrics['database_queries'] ) ) {
			$this->current_metrics['database_queries'] = array(
				'total_queries' => 0,
				'slow_queries'  => array(),
			);
		}

		$this->current_metrics['database_queries']['total_queries']++;

		// Track SELECT queries for performance analysis
		if ( strpos( strtoupper( $query ), 'SELECT' ) === 0 ) {
			$start = microtime( true );

			// Callback to run after query
			add_action(
				'shutdown',
				function() use ( $query, $start ) {
					$duration = ( microtime( true ) - $start ) * 1000; // Convert to ms
					if ( $duration > 100 ) { // Flag queries over 100ms
						if ( ! isset( $this->current_metrics['database_queries']['slow_queries'] ) ) {
							$this->current_metrics['database_queries']['slow_queries'] = array();
						}
						$this->current_metrics['database_queries']['slow_queries'][] = array(
							'query'    => substr( $query, 0, 300 ),
							'duration' => $duration,
						);
					}
				}
			);
		}

		return $query;
	}

	/**
	 * Track cache access
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return WP_Query
	 */
	public function track_cache_access( $query ) {
		if ( ! isset( $this->current_metrics['cache'] ) ) {
			$this->current_metrics['cache'] = array(
				'hits'  => 0,
				'misses' => 0,
			);
		}

		// Simple cache tracking
		if ( method_exists( $query, 'is_main_query' ) && $query->is_main_query() ) {
			// Check if from cache (simplified)
			$this->current_metrics['cache']['hits']++;
		}

		return $query;
	}

	/**
	 * Finalize metrics and store
	 */
	public function finalize_metrics() {
		$this->current_metrics['request_duration'] = ( microtime( true ) - $this->request_start_time ) * 1000; // Convert to ms
		$this->current_metrics['memory_usage'] = memory_get_usage( true ) / 1024 / 1024; // Convert to MB
		$this->current_metrics['peak_memory'] = memory_get_peak_usage( true ) / 1024 / 1024; // Convert to MB
		$this->current_metrics['timestamp'] = time();

		// Store metrics in transient for retrieval by dashboard
		$hour_key = 'flatwp_metrics_' . gmdate( 'Y-m-d-H' );
		$metrics  = get_transient( $hour_key );

		if ( ! $metrics ) {
			$metrics = array();
		}

		$metrics[] = $this->current_metrics;

		// Keep last 1000 requests
		if ( count( $metrics ) > 1000 ) {
			$metrics = array_slice( $metrics, -1000 );
		}

		set_transient( $hour_key, $metrics, HOUR_IN_SECONDS );
	}

	/**
	 * Register metrics REST endpoint
	 */
	public function register_metrics_endpoint() {
		register_rest_route(
			'flatwp/v1',
			'/metrics',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_metrics_endpoint' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			'flatwp/v1',
			'/metrics/aggregated',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_aggregated_metrics' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Get metrics endpoint
	 *
	 * @return WP_REST_Response
	 */
	public function get_metrics_endpoint() {
		$hour_key = 'flatwp_metrics_' . gmdate( 'Y-m-d-H' );
		$metrics  = get_transient( $hour_key );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'metrics'   => $metrics ? $metrics : array(),
					'timestamp' => time(),
				),
			)
		);
	}

	/**
	 * Get aggregated metrics
	 *
	 * @return WP_REST_Response
	 */
	public function get_aggregated_metrics() {
		$hour_key = 'flatwp_metrics_' . gmdate( 'Y-m-d-H' );
		$metrics  = get_transient( $hour_key );

		if ( ! $metrics || empty( $metrics ) ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'data'    => array(
						'request_count'          => 0,
						'avg_request_duration'   => 0,
						'max_request_duration'   => 0,
						'min_request_duration'   => 0,
						'total_graphql_queries'  => 0,
						'avg_graphql_duration'   => 0,
						'slow_graphql_queries'   => 0,
						'cache_hit_rate'         => 0,
						'avg_memory_usage'       => 0,
						'peak_memory_usage'      => 0,
						'total_database_queries' => 0,
						'slow_database_queries'  => 0,
					),
				)
			);
		}

		// Aggregate metrics
		$aggregated = $this->aggregate_metrics( $metrics );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $aggregated,
			)
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
			'request_count'          => count( $metrics ),
			'avg_request_duration'   => 0,
			'max_request_duration'   => 0,
			'min_request_duration'   => 0,
			'total_graphql_queries'  => 0,
			'avg_graphql_duration'   => 0,
			'slow_graphql_queries'   => 0,
			'cache_hit_rate'         => 0,
			'avg_memory_usage'       => 0,
			'peak_memory_usage'      => 0,
			'total_database_queries' => 0,
			'slow_database_queries'  => 0,
		);

		$durations       = array();
		$graphql_times   = array();
		$cache_hits      = 0;
		$memory_usage    = array();
		$db_queries      = 0;
		$slow_db_queries = 0;

		foreach ( $metrics as $metric ) {
			// Request duration
			if ( isset( $metric['request_duration'] ) ) {
				$durations[] = $metric['request_duration'];
			}

			// GraphQL queries
			if ( ! empty( $metric['graphql_queries'] ) ) {
				foreach ( $metric['graphql_queries'] as $gql_query ) {
					$aggregated['total_graphql_queries']++;
					if ( isset( $gql_query['duration'] ) ) {
						$graphql_times[] = $gql_query['duration'];
						if ( $gql_query['duration'] > 1000 ) {
							$aggregated['slow_graphql_queries']++;
						}
					}
				}
			}

			// Cache
			if ( isset( $metric['cache']['hits'] ) ) {
				$cache_hits += $metric['cache']['hits'];
			}

			// Memory
			if ( isset( $metric['memory_usage'] ) ) {
				$memory_usage[] = $metric['memory_usage'];
			}
			if ( isset( $metric['peak_memory'] ) ) {
				$peak_memory[] = $metric['peak_memory'];
			}

			// Database
			if ( isset( $metric['database_queries']['total_queries'] ) ) {
				$db_queries += $metric['database_queries']['total_queries'];
			}
			if ( ! empty( $metric['database_queries']['slow_queries'] ) ) {
				$slow_db_queries += count( $metric['database_queries']['slow_queries'] );
			}
		}

		// Calculate aggregates
		if ( ! empty( $durations ) ) {
			$aggregated['avg_request_duration'] = array_sum( $durations ) / count( $durations );
			$aggregated['max_request_duration'] = max( $durations );
			$aggregated['min_request_duration'] = min( $durations );
		}

		if ( ! empty( $graphql_times ) ) {
			$aggregated['avg_graphql_duration'] = array_sum( $graphql_times ) / count( $graphql_times );
		}

		if ( $cache_hits > 0 ) {
			$aggregated['cache_hit_rate'] = ( $cache_hits / ( $cache_hits + 1 ) ) * 100;
		}

		if ( ! empty( $memory_usage ) ) {
			$aggregated['avg_memory_usage']  = array_sum( $memory_usage ) / count( $memory_usage );
			$aggregated['peak_memory_usage'] = max( $memory_usage );
		}

		$aggregated['total_database_queries'] = $db_queries;
		$aggregated['slow_database_queries']  = $slow_db_queries;

		return $aggregated;
	}

	/**
	 * Check admin permission
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get current metrics
	 *
	 * @return array
	 */
	public function get_current_metrics() {
		return $this->current_metrics;
	}
}
