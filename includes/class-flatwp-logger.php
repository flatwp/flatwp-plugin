<?php
/**
 * Logger Class for FlatWP Plugin
 *
 * Handles all logging functionality including webhook calls, errors, and debug information
 *
 * @package FlatWP
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP Logger Class
 */
class FlatWP_Logger {

	/**
	 * Database table name
	 */
	const TABLE_NAME = 'flatwp_logs';

	/**
	 * Log types
	 */
	const TYPE_WEBHOOK = 'webhook';
	const TYPE_MANUAL = 'manual';
	const TYPE_SYSTEM = 'system';
	const TYPE_ERROR = 'error';
	const TYPE_DEBUG = 'debug';
	const TYPE_GRAPHQL = 'graphql';
	const TYPE_CACHE = 'cache';
	const TYPE_USER_ACTION = 'user_action';
	const TYPE_SETTINGS = 'settings';
	const TYPE_PREVIEW = 'preview';

	/**
	 * Log severities
	 */
	const SEVERITY_SUCCESS = 'success';
	const SEVERITY_ERROR = 'error';
	const SEVERITY_WARNING = 'warning';
	const SEVERITY_INFO = 'info';

	/**
	 * Maximum number of logs to keep
	 */
	private $max_logs = 100;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->max_logs = get_option( 'flatwp_max_logs', 100 );
	}

	/**
	 * Create logs database table
	 */
	public function create_logs_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			timestamp bigint(20) NOT NULL,
			type varchar(50) NOT NULL,
			severity varchar(20) NOT NULL,
			message text NOT NULL,
			context longtext DEFAULT NULL,
			response_data longtext DEFAULT NULL,
			response_time int(11) DEFAULT NULL,
			http_code int(11) DEFAULT NULL,
			user_id bigint(20) DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY type (type),
			KEY severity (severity),
			KEY timestamp (timestamp)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Store table version for future migrations
		update_option( 'flatwp_logs_db_version', '1.0' );
	}

	/**
	 * Log an event
	 *
	 * @param string $type Log type.
	 * @param string $severity Log severity.
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 * @param array  $response Response data (for webhooks).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log( $type, $severity, $message, $context = array(), $response = array() ) {
		global $wpdb;

		// Check if debug mode is enabled
		$debug_enabled = flatwp()->get_setting( 'enable_debug', false );

		// ALWAYS log these types regardless of debug mode
		$always_log_types = array(
			self::TYPE_WEBHOOK,
			self::TYPE_ERROR,
			self::TYPE_SYSTEM,
			self::TYPE_GRAPHQL,
			self::TYPE_CACHE,
			self::TYPE_USER_ACTION,
			self::TYPE_SETTINGS,
			self::TYPE_PREVIEW,
		);

		// ALWAYS log these severities regardless of debug mode
		$always_log_severities = array(
			self::SEVERITY_ERROR,
			self::SEVERITY_WARNING,
			self::SEVERITY_SUCCESS,
		);

		// Only filter TYPE_DEBUG with SEVERITY_INFO when debug mode is off
		if ( ! $debug_enabled && $type === self::TYPE_DEBUG && $severity === self::SEVERITY_INFO ) {
			return false;
		}

		// Always log if it's an important type or severity
		// This ensures webhooks, cache, graphql, user actions, etc. always log
		$is_important = in_array( $type, $always_log_types, true )
			|| in_array( $severity, $always_log_severities, true );

		if ( ! $debug_enabled && ! $is_important ) {
			return false;
		}

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$data = array(
			'timestamp'     => current_time( 'timestamp' ),
			'type'          => sanitize_text_field( $type ),
			'severity'      => sanitize_text_field( $severity ),
			'message'       => sanitize_text_field( $message ),
			'context'       => ! empty( $context ) ? wp_json_encode( $context ) : null,
			'response_data' => ! empty( $response['data'] ) ? wp_json_encode( $response['data'] ) : null,
			'response_time' => isset( $response['response_time'] ) ? absint( $response['response_time'] ) : null,
			'http_code'     => isset( $response['http_code'] ) ? absint( $response['http_code'] ) : null,
			'user_id'       => get_current_user_id(),
		);

		$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d' );

		$result = $wpdb->insert( $table_name, $data, $format );

		if ( $result ) {
			$log_id = $wpdb->insert_id;

			// Clean up old logs
			$this->cleanup_old_logs();

			return $log_id;
		}

		return false;
	}

	/**
	 * Log a webhook call
	 *
	 * @param string $message Log message.
	 * @param array  $paths Paths being revalidated.
	 * @param array  $response Response data from Next.js.
	 * @param float  $response_time Response time in milliseconds.
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_webhook( $message, $paths, $response, $response_time ) {
		$severity = $this->determine_severity( $response );

		$context = array(
			'paths'       => $paths,
			'path_count'  => count( $paths ),
		);

		$response_data = array(
			'data'          => $response,
			'response_time' => $response_time,
			'http_code'     => isset( $response['code'] ) ? $response['code'] : null,
		);

		return $this->log(
			self::TYPE_WEBHOOK,
			$severity,
			$message,
			$context,
			$response_data
		);
	}

	/**
	 * Log an error
	 *
	 * @param string $message Error message.
	 * @param array  $context Error context (stack trace, etc.).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_error( $message, $context = array() ) {
		// Always log errors regardless of debug mode
		return $this->log(
			self::TYPE_ERROR,
			self::SEVERITY_ERROR,
			$message,
			$context
		);
	}

	/**
	 * Log a debug message
	 *
	 * @param string $message Debug message.
	 * @param array  $context Debug context.
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_debug( $message, $context = array() ) {
		return $this->log(
			self::TYPE_DEBUG,
			self::SEVERITY_INFO,
			$message,
			$context
		);
	}

	/**
	 * Log a GraphQL query
	 *
	 * @param string $message Log message.
	 * @param array  $context Query context (operation, fields, variables).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_graphql( $message, $context = array() ) {
		return $this->log(
			self::TYPE_GRAPHQL,
			self::SEVERITY_INFO,
			$message,
			$context
		);
	}

	/**
	 * Log a cache operation
	 *
	 * @param string $message Log message.
	 * @param string $severity Severity level (success, warning, error).
	 * @param array  $context Cache operation context.
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_cache( $message, $severity = self::SEVERITY_INFO, $context = array() ) {
		return $this->log(
			self::TYPE_CACHE,
			$severity,
			$message,
			$context
		);
	}

	/**
	 * Log a user action
	 *
	 * @param string $message Log message.
	 * @param array  $context Action context (user, action, target).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_user_action( $message, $context = array() ) {
		return $this->log(
			self::TYPE_USER_ACTION,
			self::SEVERITY_INFO,
			$message,
			$context
		);
	}

	/**
	 * Log a settings change
	 *
	 * @param string $message Log message.
	 * @param array  $context Settings context (key, old_value, new_value).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_settings( $message, $context = array() ) {
		return $this->log(
			self::TYPE_SETTINGS,
			self::SEVERITY_INFO,
			$message,
			$context
		);
	}

	/**
	 * Log a preview action
	 *
	 * @param string $message Log message.
	 * @param array  $context Preview context (post_id, url, token).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_preview( $message, $context = array() ) {
		return $this->log(
			self::TYPE_PREVIEW,
			self::SEVERITY_INFO,
			$message,
			$context
		);
	}

	/**
	 * Get logs
	 *
	 * @param array $args Query arguments.
	 * @return array Array of log entries.
	 */
	public function get_logs( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'page'     => 1,
			'per_page' => 20,
			'type'     => 'all',
			'severity' => 'all',
			'orderby'  => 'timestamp',
			'order'    => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$where = array( '1=1' );

		// Filter by type
		if ( $args['type'] !== 'all' ) {
			$where[] = $wpdb->prepare( 'type = %s', $args['type'] );
		}

		// Filter by severity
		if ( $args['severity'] !== 'all' ) {
			$where[] = $wpdb->prepare( 'severity = %s', $args['severity'] );
		}

		$where_clause = implode( ' AND ', $where );

		// Get total count
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}" );

		// Calculate offset
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Get logs
		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );

		$logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d",
				$args['per_page'],
				$offset
			),
			ARRAY_A
		);

		// Decode JSON fields
		foreach ( $logs as &$log ) {
			if ( ! empty( $log['context'] ) ) {
				$log['context'] = json_decode( $log['context'], true );
			}
			if ( ! empty( $log['response_data'] ) ) {
				$log['response_data'] = json_decode( $log['response_data'], true );
			}
		}

		return array(
			'logs'  => $logs,
			'total' => (int) $total,
			'pages' => ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Get error logs only
	 *
	 * @param int $limit Number of logs to retrieve.
	 * @return array Array of error log entries.
	 */
	public function get_error_logs( $limit = 20 ) {
		return $this->get_logs(
			array(
				'severity' => self::SEVERITY_ERROR,
				'per_page' => $limit,
			)
		);
	}

	/**
	 * Get error count
	 *
	 * @param int $since_timestamp Get errors since this timestamp.
	 * @return int Error count.
	 */
	public function get_error_count( $since_timestamp = null ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = "severity = '" . self::SEVERITY_ERROR . "'";

		if ( $since_timestamp ) {
			$where .= $wpdb->prepare( ' AND timestamp >= %d', $since_timestamp );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$where}" );

		return (int) $count;
	}

	/**
	 * Clear all logs
	 *
	 * @return bool True on success, false on failure.
	 */
	public function clear_logs() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$result = $wpdb->query( "TRUNCATE TABLE {$table_name}" );

		if ( $result !== false ) {
			$this->log(
				self::TYPE_SYSTEM,
				self::SEVERITY_INFO,
				__( 'All logs cleared', 'flatwp-wordpress-connector' )
			);
			return true;
		}

		return false;
	}

	/**
	 * Clear logs by type
	 *
	 * @param string $type Log type to clear.
	 * @return bool True on success, false on failure.
	 */
	public function clear_logs_by_type( $type ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$result = $wpdb->delete(
			$table_name,
			array( 'type' => $type ),
			array( '%s' )
		);

		return $result !== false;
	}

	/**
	 * Export logs
	 *
	 * @param string $format Export format (json or csv).
	 * @param array  $args Query arguments.
	 * @return string|false Exported data or false on failure.
	 */
	public function export_logs( $format = 'json', $args = array() ) {
		$args['per_page'] = 1000; // Export up to 1000 logs
		$logs_data = $this->get_logs( $args );
		$logs = $logs_data['logs'];

		if ( empty( $logs ) ) {
			return false;
		}

		if ( $format === 'csv' ) {
			return $this->export_as_csv( $logs );
		}

		// Default to JSON
		return wp_json_encode( $logs, JSON_PRETTY_PRINT );
	}

	/**
	 * Export logs as CSV
	 *
	 * @param array $logs Logs to export.
	 * @return string CSV data.
	 */
	private function export_as_csv( $logs ) {
		$output = fopen( 'php://temp', 'r+' );

		// CSV headers
		$headers = array( 'ID', 'Timestamp', 'Type', 'Severity', 'Message', 'Response Time (ms)', 'HTTP Code', 'User ID' );
		fputcsv( $output, $headers );

		// CSV rows
		foreach ( $logs as $log ) {
			$row = array(
				$log['id'],
				date( 'Y-m-d H:i:s', $log['timestamp'] ),
				$log['type'],
				$log['severity'],
				$log['message'],
				$log['response_time'] ?? '',
				$log['http_code'] ?? '',
				$log['user_id'] ?? '',
			);
			fputcsv( $output, $row );
		}

		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );

		return $csv;
	}

	/**
	 * Cleanup old logs
	 *
	 * Keeps only the most recent logs based on max_logs setting
	 */
	private function cleanup_old_logs() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Get total count
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

		// If we're over the limit, delete oldest logs
		if ( $total > $this->max_logs ) {
			$to_delete = $total - $this->max_logs;

			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$table_name} ORDER BY timestamp ASC LIMIT %d",
					$to_delete
				)
			);
		}
	}

	/**
	 * Determine severity from response
	 *
	 * @param array $response Response data.
	 * @return string Severity level.
	 */
	private function determine_severity( $response ) {
		if ( is_wp_error( $response ) ) {
			return self::SEVERITY_ERROR;
		}

		if ( isset( $response['code'] ) ) {
			$code = $response['code'];

			if ( $code >= 200 && $code < 300 ) {
				return self::SEVERITY_SUCCESS;
			}

			if ( $code >= 400 && $code < 500 ) {
				return self::SEVERITY_WARNING;
			}

			if ( $code >= 500 ) {
				return self::SEVERITY_ERROR;
			}
		}

		return self::SEVERITY_INFO;
	}

	/**
	 * Get statistics
	 *
	 * @return array Log statistics.
	 */
	public function get_statistics() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$stats = array(
			'total_logs'    => 0,
			'by_type'       => array(),
			'by_severity'   => array(),
			'error_count'   => 0,
			'warning_count' => 0,
			'success_count' => 0,
		);

		// Total logs
		$stats['total_logs'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

		// By type
		$type_counts = $wpdb->get_results(
			"SELECT type, COUNT(*) as count FROM {$table_name} GROUP BY type",
			ARRAY_A
		);
		foreach ( $type_counts as $row ) {
			$stats['by_type'][ $row['type'] ] = (int) $row['count'];
		}

		// By severity
		$severity_counts = $wpdb->get_results(
			"SELECT severity, COUNT(*) as count FROM {$table_name} GROUP BY severity",
			ARRAY_A
		);
		foreach ( $severity_counts as $row ) {
			$stats['by_severity'][ $row['severity'] ] = (int) $row['count'];

			// Also set direct counts for frontend
			if ( $row['severity'] === self::SEVERITY_ERROR ) {
				$stats['error_count'] = (int) $row['count'];
			} elseif ( $row['severity'] === self::SEVERITY_WARNING ) {
				$stats['warning_count'] = (int) $row['count'];
			} elseif ( $row['severity'] === self::SEVERITY_SUCCESS ) {
				$stats['success_count'] = (int) $row['count'];
			}
		}

		return $stats;
	}
}
