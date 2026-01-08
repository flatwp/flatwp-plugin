<?php
/**
 * FlatWP Webhook Metrics Class
 *
 * Tracks webhook delivery metrics and provides monitoring capabilities.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Metrics {

	/**
	 * Webhook metrics table name
	 */
	const TABLE_NAME = 'flatwp_webhook_metrics';

	/**
	 * Record webhook delivery
	 */
	public static function record_delivery( $target_id, $event_type, $status, $duration_ms, $response_code = null, $error_message = null ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$wpdb->insert(
			$table,
			array(
				'target_id'      => $target_id,
				'event_type'     => $event_type,
				'status'         => $status,
				'duration_ms'    => (int) $duration_ms,
				'response_code'  => $response_code ? (int) $response_code : null,
				'error_message'  => $error_message ? sanitize_text_field( $error_message ) : null,
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get metrics for a target
	 */
	public static function get_target_metrics( $target_id, $days = 7 ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$metrics = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					status,
					COUNT(*) as count,
					AVG(duration_ms) as avg_duration,
					MIN(duration_ms) as min_duration,
					MAX(duration_ms) as max_duration,
					MIN(created_at) as first_at,
					MAX(created_at) as last_at
				FROM {$table}
				WHERE target_id = %s
				AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
				GROUP BY status",
				$target_id,
				$days
			)
		);

		return $metrics;
	}

	/**
	 * Get overall webhook health
	 */
	public static function get_health( $days = 7 ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) as total,
					SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
					SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
					SUM(CASE WHEN status = 'retried' THEN 1 ELSE 0 END) as retried_count,
					AVG(duration_ms) as avg_duration,
					MAX(created_at) as last_sent
				FROM {$table}
				WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);

		if ( ! $stats || ! $stats->total ) {
			return array(
				'total'        => 0,
				'success_rate' => 0,
				'avg_duration' => 0,
				'last_sent'    => null,
			);
		}

		return array(
			'total'        => (int) $stats->total,
			'success_count' => (int) $stats->success_count,
			'failed_count'  => (int) $stats->failed_count,
			'retried_count' => (int) $stats->retried_count,
			'success_rate'  => round( ( $stats->success_count / $stats->total ) * 100, 2 ),
			'avg_duration'  => round( (float) $stats->avg_duration, 2 ),
			'last_sent'     => $stats->last_sent,
		);
	}

	/**
	 * Get recent errors
	 */
	public static function get_recent_errors( $limit = 10 ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					id,
					target_id,
					event_type,
					error_message,
					response_code,
					duration_ms,
					created_at
				FROM {$table}
				WHERE status = 'failed'
				AND error_message IS NOT NULL
				ORDER BY created_at DESC
				LIMIT %d",
				$limit
			)
		);
	}

	/**
	 * Clear old metrics
	 */
	public static function cleanup_old_metrics( $days = 30 ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table}
				WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);
	}

	/**
	 * Create metrics table
	 */
	public static function create_table() {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			target_id VARCHAR(64) NOT NULL,
			event_type VARCHAR(64) NOT NULL,
			status VARCHAR(32) NOT NULL,
			duration_ms INT UNSIGNED,
			response_code INT,
			error_message TEXT,
			created_at DATETIME NOT NULL,
			KEY target_id_idx (target_id),
			KEY status_idx (status),
			KEY created_at_idx (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
