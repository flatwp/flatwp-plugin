<?php
/**
 * FlatWP Log GraphQL Resolver
 *
 * Resolves GraphQL queries for FlatWP activity logs
 *
 * @package FlatWP
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP Log GraphQL Resolver
 */
class FlatWP_Log_Resolver {

	/**
	 * Resolve logs connection query
	 *
	 * @param array $args Query arguments.
	 * @return array FlatWP logs connection data.
	 */
	public static function resolve_logs( $args ) {
		// Get the logger instance
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return array(
				'edges'      => array(),
				'pageInfo'   => array(
					'hasNextPage'     => false,
					'hasPreviousPage' => false,
					'startCursor'     => null,
					'endCursor'       => null,
				),
				'total'      => 0,
				'totalPages' => 0,
			);
		}

		// Parse cursor for pagination
		$page = 1;
		if ( ! empty( $args['after'] ) ) {
			$cursor_data = self::decode_cursor( $args['after'] );
			if ( $cursor_data ) {
				$page = $cursor_data['page'] + 1;
			}
		}

		// Prepare query args
		$query_args = array(
			'page'     => $page,
			'per_page' => absint( $args['first'] ?? 20 ),
			'type'     => sanitize_text_field( $args['type'] ?? 'all' ),
			'severity' => sanitize_text_field( $args['severity'] ?? 'all' ),
			'orderby'  => sanitize_text_field( $args['orderBy'] ?? 'timestamp' ),
			'order'    => strtoupper( sanitize_text_field( $args['order'] ?? 'DESC' ) ),
		);

		// Get logs from database
		$logs_data = $logger->get_logs( $query_args );

		if ( empty( $logs_data['logs'] ) ) {
			return array(
				'edges'      => array(),
				'pageInfo'   => array(
					'hasNextPage'     => false,
					'hasPreviousPage' => false,
					'startCursor'     => null,
					'endCursor'       => null,
				),
				'total'      => 0,
				'totalPages' => 0,
			);
		}

		// Build edges
		$edges = array();
		foreach ( $logs_data['logs'] as $index => $log ) {
			$cursor = self::encode_cursor(
				array(
					'page'  => $page,
					'index' => $index,
				)
			);

			$edges[] = array(
				'node'   => self::transform_log( $log ),
				'cursor' => $cursor,
			);
		}

		// Calculate has next page
		$has_next_page = $page < $logs_data['pages'];
		$has_previous_page = $page > 1;

		// Get cursors
		$start_cursor = ! empty( $edges ) ? $edges[0]['cursor'] : null;
		$end_cursor = ! empty( $edges ) ? end( $edges )['cursor'] : null;

		return array(
			'edges'      => $edges,
			'pageInfo'   => array(
				'hasNextPage'     => $has_next_page,
				'hasPreviousPage' => $has_previous_page,
				'startCursor'     => $start_cursor,
				'endCursor'       => $end_cursor,
			),
			'total'      => $logs_data['total'],
			'totalPages' => $logs_data['pages'],
		);
	}

	/**
	 * Resolve recent activity
	 *
	 * @param array $args Query arguments.
	 * @return array Array of recent log entries.
	 */
	public static function resolve_recent_activity( $args ) {
		// Get the logger instance
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return array();
		}

		$limit = absint( $args['limit'] ?? 10 );

		// Get logs
		$logs_data = $logger->get_logs(
			array(
				'page'     => 1,
				'per_page' => $limit,
				'order'    => 'DESC',
			)
		);

		if ( empty( $logs_data['logs'] ) ) {
			return array();
		}

		// Transform logs
		$transformed = array();
		foreach ( $logs_data['logs'] as $log ) {
			$transformed[] = self::transform_log( $log );
		}

		return $transformed;
	}

	/**
	 * Get error count
	 *
	 * @param int $since_timestamp Get errors since this timestamp.
	 * @return int Error count.
	 */
	public static function get_error_count( $since_timestamp = null ) {
		// Get the logger instance
		$logger = flatwp()->logger;

		if ( ! $logger ) {
			return 0;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'flatwp_logs';
		$where = array( "severity = 'error'" );

		if ( $since_timestamp ) {
			$where[] = $wpdb->prepare( 'timestamp >= %d', $since_timestamp );
		}

		$where_clause = implode( ' AND ', $where );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom plugin logging table requires direct queries; table name safely uses $wpdb->prefix
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}" );

		return $count;
	}

	/**
	 * Transform log entry for GraphQL response
	 *
	 * @param array $log Log entry from database.
	 * @return array Transformed log entry.
	 */
	private static function transform_log( $log ) {
		// Ensure ID is a string
		if ( isset( $log['id'] ) ) {
			$log['id'] = (string) $log['id'];
		}

		// Decode JSON fields if they're strings
		if ( isset( $log['context'] ) && is_string( $log['context'] ) && ! empty( $log['context'] ) ) {
			$log['context'] = json_decode( $log['context'], true );
		}

		if ( isset( $log['response_data'] ) && is_string( $log['response_data'] ) && ! empty( $log['response_data'] ) ) {
			$log['response_data'] = json_decode( $log['response_data'], true );
		}

		return $log;
	}

	/**
	 * Encode pagination cursor
	 *
	 * @param array $data Cursor data.
	 * @return string Encoded cursor.
	 */
	private static function encode_cursor( $data ) {
		return base64_encode( wp_json_encode( $data ) ); // phpcs:ignore WordPress.PHP.DiscouragedFunctions.obfuscation_base64_encode
	}

	/**
	 * Decode pagination cursor
	 *
	 * @param string $cursor Encoded cursor.
	 * @return array|null Decoded cursor data or null if invalid.
	 */
	private static function decode_cursor( $cursor ) {
		// phpcs:ignore WordPress.PHP.DiscouragedFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $cursor );

		if ( ! $decoded ) {
			return null;
		}

		$data = json_decode( $decoded, true );

		return is_array( $data ) ? $data : null;
	}
}
