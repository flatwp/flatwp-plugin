<?php
/**
 * FlatWP Webhook Dispatcher
 *
 * Handles webhook delivery with retry logic, multiple targets, and failover.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Dispatcher {

	/**
	 * Dispatch webhook to all enabled targets
	 */
	public static function dispatch( $event_type, $event_data = array() ) {
		$targets = FlatWP_Webhook_Config::get_enabled_targets();

		if ( empty( $targets ) ) {
			do_action( 'flatwp_webhook_no_targets', $event_type );
			return new WP_Error( 'no_targets', 'No webhook targets configured' );
		}

		// Build payload using registered plugins
		$payload = FlatWP_Webhook_Plugin_Manager::build_payload( $event_type, $event_data );

		$results = array();

		// Send to each target
		foreach ( $targets as $target ) {
			$result = self::send_to_target( $target, $event_type, $payload );
			$results[ $target['id'] ] = $result;
		}

		// Fire action with results
		do_action( 'flatwp_webhook_dispatched', $event_type, $results );

		return $results;
	}

	/**
	 * Send webhook to a specific target
	 */
	public static function send_to_target( $target, $event_type, $payload = array() ) {
		if ( empty( $target['url'] ) || empty( $target['enabled'] ) ) {
			return new WP_Error( 'target_disabled', 'Target is disabled or incomplete' );
		}

		$start_time = microtime( true );
		$attempt = 0;
		$max_retries = isset( $target['retries'] ) ? (int) $target['retries'] : 3;
		$timeout = isset( $target['timeout'] ) ? (int) $target['timeout'] : 15;

		// Prepare payload
		$payload_data = array(
			'event_type' => $event_type,
			'data'       => $payload,
			'_meta'      => array(
				'sent_at'        => current_time( 'mysql' ),
				'version'        => defined( 'FLATWP_VERSION' ) ? FLATWP_VERSION : '0.3.0',
				'wordpress_site' => home_url(),
				'target_id'      => $target['id'],
			),
		);

		$json_payload = wp_json_encode( $payload_data );

		// Prepare request arguments
		$args = array(
			'body'      => $json_payload,
			'headers'   => self::get_headers( $target, $json_payload ),
			'timeout'   => $timeout,
			'blocking'  => apply_filters( 'flatwp_webhook_blocking', false, $target ),
			'sslverify' => apply_filters( 'flatwp_webhook_sslverify', true, $target ),
		);

		// Merge custom headers
		if ( ! empty( $target['headers'] ) && is_array( $target['headers'] ) ) {
			$args['headers'] = array_merge( $args['headers'], $target['headers'] );
		}

		// Attempt delivery with retries
		$response = null;
		$last_error = null;

		while ( $attempt <= $max_retries ) {
			$response = wp_remote_post( $target['url'], $args );

			if ( ! is_wp_error( $response ) ) {
				$duration = round( ( microtime( true ) - $start_time ) * 1000, 2 );

				// Log success
				if ( class_exists( 'FlatWP_Webhook_Metrics' ) ) {
					FlatWP_Webhook_Metrics::record_delivery(
						$target['id'],
						$event_type,
						'success',
						$duration,
						wp_remote_retrieve_response_code( $response )
					);
				}

				return array(
					'status'       => 'success',
					'target_id'    => $target['id'],
					'response_code' => wp_remote_retrieve_response_code( $response ),
					'duration_ms'  => $duration,
					'attempts'     => $attempt + 1,
				);
			}

			$last_error = $response->get_error_message();
			$attempt++;

			// Wait before retry (exponential backoff)
			if ( $attempt <= $max_retries ) {
				sleep( pow( 2, $attempt - 1 ) );
			}
		}

		$duration = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		// Log failure
		if ( class_exists( 'FlatWP_Webhook_Metrics' ) ) {
			FlatWP_Webhook_Metrics::record_delivery(
				$target['id'],
				$event_type,
				'failed',
				$duration,
				null,
				$last_error
			);
		}

		return array(
			'status'      => 'failed',
			'target_id'   => $target['id'],
			'error'       => $last_error,
			'duration_ms' => $duration,
			'attempts'    => $attempt,
		);
	}

	/**
	 * Get headers for webhook request
	 */
	private static function get_headers( $target, $payload ) {
		$headers = array(
			'Content-Type'   => 'application/json',
			'User-Agent'     => 'FlatWP-Webhooks/' . ( defined( 'FLATWP_VERSION' ) ? FLATWP_VERSION : '0.3.0' ),
			'X-FlatWP-Event' => sanitize_key( '' ),
		);

		// Add HMAC signature if secret is configured
		$secret = get_option( 'flatwp_api_secret' );
		if ( ! empty( $secret ) ) {
			$signature = hash_hmac( 'sha256', $payload, $secret );
			$headers['X-FlatWP-Signature'] = $signature;
			$headers['X-FlatWP-Timestamp'] = (string) time();
		}

		return apply_filters( 'flatwp_webhook_headers', $headers, $target );
	}

	/**
	 * Test webhook delivery
	 */
	public static function test( $target_id ) {
		$targets = FlatWP_Webhook_Config::get_enabled_targets();
		$target = null;

		foreach ( $targets as $t ) {
			if ( $t['id'] === $target_id ) {
				$target = $t;
				break;
			}
		}

		if ( ! $target ) {
			return new WP_Error( 'target_not_found', 'Webhook target not found' );
		}

		// Build test payload
		$test_data = array(
			'event_type' => 'test',
			'test'       => true,
			'message'    => 'This is a test webhook from FlatWP',
		);

		// Force blocking mode for tests
		add_filter( 'flatwp_webhook_blocking', '__return_true' );
		$result = self::send_to_target( $target, 'test', $test_data );
		remove_filter( 'flatwp_webhook_blocking', '__return_true' );

		return $result;
	}
}
