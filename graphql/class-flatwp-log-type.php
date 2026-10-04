<?php
/**
 * FlatWP Log GraphQL Type
 *
 * Defines the GraphQL type for activity logs from the FlatWP logger
 *
 * @package FlatWP
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP Log GraphQL Type
 */
class FlatWP_Log_Type {

	/**
	 * Register the GraphQL type and queries
	 */
	public static function register() {
		if ( ! function_exists( 'register_graphql_type' ) || ! function_exists( 'register_graphql_field' ) ) {
			return;
		}

		// Register the FlatWPLog type
		self::register_log_type();

		// Register the FlatWPLogs connection type
		self::register_logs_connection_type();

		// Register the query fields
		self::register_query_fields();
	}

	/**
	 * Register the FlatWPLog type
	 */
	private static function register_log_type() {
		register_graphql_type(
			'FlatWPLog',
			array(
				'description' => __( 'A FlatWP activity log entry', 'flatwp-headless-connector' ),
				'fields'      => array(
					'id'           => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'The unique identifier for the log entry', 'flatwp-headless-connector' ),
					),
					'timestamp'    => array(
						'type'        => array( 'non_null' => 'Int' ),
						'description' => __( 'The Unix timestamp when the log was created', 'flatwp-headless-connector' ),
					),
					'type'         => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'The log type (webhook, manual, system, error, debug)', 'flatwp-headless-connector' ),
					),
					'severity'     => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'The log severity (success, error, warning, info)', 'flatwp-headless-connector' ),
					),
					'message'      => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'The log message', 'flatwp-headless-connector' ),
					),
					'context'      => array(
						'type'        => 'String',
						'description' => __( 'Additional context data (JSON string)', 'flatwp-headless-connector' ),
						'resolve'     => function( $log ) {
							return isset( $log['context'] ) && is_array( $log['context'] ) ? wp_json_encode( $log['context'] ) : null;
						},
					),
					'responseData' => array(
						'type'        => 'String',
						'description' => __( 'Response data from the webhook (JSON string)', 'flatwp-headless-connector' ),
						'resolve'     => function( $log ) {
							return isset( $log['response_data'] ) && is_array( $log['response_data'] ) ? wp_json_encode( $log['response_data'] ) : null;
						},
					),
					'responseTime' => array(
						'type'        => 'Int',
						'description' => __( 'Response time in milliseconds', 'flatwp-headless-connector' ),
						'resolve'     => function( $log ) {
							return isset( $log['response_time'] ) ? absint( $log['response_time'] ) : null;
						},
					),
					'httpCode'     => array(
						'type'        => 'Int',
						'description' => __( 'HTTP response code from the webhook', 'flatwp-headless-connector' ),
						'resolve'     => function( $log ) {
							return isset( $log['http_code'] ) ? absint( $log['http_code'] ) : null;
						},
					),
					'userId'       => array(
						'type'        => 'Int',
						'description' => __( 'WordPress user ID who triggered the log', 'flatwp-headless-connector' ),
						'resolve'     => function( $log ) {
							return isset( $log['user_id'] ) ? absint( $log['user_id'] ) : null;
						},
					),
					'user'         => array(
						'type'        => 'User',
						'description' => __( 'The WordPress user who triggered the log', 'flatwp-headless-connector' ),
						'resolve'     => function( $log ) {
							if ( ! isset( $log['user_id'] ) || ! $log['user_id'] ) {
								return null;
							}

							$user = get_user_by( 'id', $log['user_id'] );
							return $user ? $user : null;
						},
					),
				),
			)
		);
	}

	/**
	 * Register the FlatWPLogs connection type
	 */
	private static function register_logs_connection_type() {
		register_graphql_type(
			'FlatWPLogsConnection',
			array(
				'description' => __( 'A connection for FlatWP activity logs', 'flatwp-headless-connector' ),
				'fields'      => array(
					'edges'        => array(
						'type'        => array( 'list_of' => 'FlatWPLogEdge' ),
						'description' => __( 'The edges of the connection', 'flatwp-headless-connector' ),
					),
					'pageInfo'     => array(
						'type'        => 'PageInfo',
						'description' => __( 'Information about the current page', 'flatwp-headless-connector' ),
					),
					'total'        => array(
						'type'        => 'Int',
						'description' => __( 'The total number of logs', 'flatwp-headless-connector' ),
					),
					'totalPages'   => array(
						'type'        => 'Int',
						'description' => __( 'The total number of pages', 'flatwp-headless-connector' ),
					),
				),
			)
		);

		register_graphql_type(
			'FlatWPLogEdge',
			array(
				'description' => __( 'An edge in a FlatWP logs connection', 'flatwp-headless-connector' ),
				'fields'      => array(
					'node' => array(
						'type'        => 'FlatWPLog',
						'description' => __( 'The log entry', 'flatwp-headless-connector' ),
					),
					'cursor' => array(
						'type'        => 'String',
						'description' => __( 'The cursor for pagination', 'flatwp-headless-connector' ),
					),
				),
			)
		);
	}

	/**
	 * Register the query fields for accessing logs
	 */
	private static function register_query_fields() {
		register_graphql_field(
			'RootQuery',
			'flatwpLogs',
			array(
				'type'        => 'FlatWPLogsConnection',
				'description' => __( 'Get FlatWP activity logs', 'flatwp-headless-connector' ),
				'args'        => array(
					'first'    => array(
						'type'        => 'Int',
						'description' => __( 'Number of logs to return', 'flatwp-headless-connector' ),
						'defaultValue' => 20,
					),
					'after'    => array(
						'type'        => 'String',
						'description' => __( 'Cursor for pagination', 'flatwp-headless-connector' ),
					),
					'type'     => array(
						'type'        => 'String',
						'description' => __( 'Filter by log type (webhook, manual, system, error, debug, or all)', 'flatwp-headless-connector' ),
						'defaultValue' => 'all',
					),
					'severity' => array(
						'type'        => 'String',
						'description' => __( 'Filter by severity (success, error, warning, info, or all)', 'flatwp-headless-connector' ),
						'defaultValue' => 'all',
					),
					'orderBy'  => array(
						'type'        => 'String',
						'description' => __( 'Field to order by (timestamp or id)', 'flatwp-headless-connector' ),
						'defaultValue' => 'timestamp',
					),
					'order'    => array(
						'type'        => 'String',
						'description' => __( 'Order direction (ASC or DESC)', 'flatwp-headless-connector' ),
						'defaultValue' => 'DESC',
					),
				),
				'resolve'     => function( $root, $args ) {
					return FlatWP_Log_Resolver::resolve_logs( $args );
				},
			)
		);

		// Register a field to get error count
		register_graphql_field(
			'RootQuery',
			'flatwpErrorCount',
			array(
				'type'        => 'Int',
				'description' => __( 'Get the count of error logs in the last 24 hours', 'flatwp-headless-connector' ),
				'resolve'     => function() {
					$since = current_time( 'timestamp' ) - ( 24 * HOUR_IN_SECONDS );
					return FlatWP_Log_Resolver::get_error_count( $since );
				},
			)
		);

		// Register a field to get recent activity
		register_graphql_field(
			'RootQuery',
			'flatwpRecentActivity',
			array(
				'type'        => array( 'list_of' => 'FlatWPLog' ),
				'description' => __( 'Get recent FlatWP activity logs', 'flatwp-headless-connector' ),
				'args'        => array(
					'limit' => array(
						'type'        => 'Int',
						'description' => __( 'Number of logs to return', 'flatwp-headless-connector' ),
						'defaultValue' => 10,
					),
				),
				'resolve'     => function( $root, $args ) {
					return FlatWP_Log_Resolver::resolve_recent_activity( $args );
				},
			)
		);
	}
}
