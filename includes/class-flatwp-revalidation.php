<?php
/**
 * FlatWP Revalidation Class
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Revalidation {

	/**
	 * Security instance
	 *
	 * @var FlatWP_Security
	 */
	private $security;

	public function __construct() {
		$this->security = new FlatWP_Security();

		add_action( 'save_post', array( $this, 'handle_post_save' ), 10, 2 );
		add_action( 'transition_post_status', array( $this, 'handle_status_change' ), 10, 3 );
	}

	/**
	 * Get revalidation settings
	 */
	public function get_settings() {
		$default_settings = array(
			'global_enabled'       => true,
			'excluded_post_types'  => array(),
			'rules'                => $this->get_default_rules(),
		);

		$settings = get_option( 'flatwp_revalidation_settings', $default_settings );

		// Ensure rules exist for all public post types
		$public_types = $this->get_public_post_types();
		foreach ( $public_types as $post_type ) {
			if ( ! isset( $settings['rules'][ $post_type ] ) ) {
				$settings['rules'][ $post_type ] = $this->get_default_rule_for_post_type( $post_type );
			}
		}

		return $settings;
	}

	/**
	 * Update revalidation settings
	 */
	public function update_settings( $new_settings ) {
		$current_settings = $this->get_settings();
		$updated_settings = array_merge( $current_settings, $new_settings );

		update_option( 'flatwp_revalidation_settings', $updated_settings );

		return $updated_settings;
	}

	/**
	 * Get default rules for all post types
	 */
	private function get_default_rules() {
		$rules = array();
		$public_types = $this->get_public_post_types();

		foreach ( $public_types as $post_type ) {
			$rules[ $post_type ] = $this->get_default_rule_for_post_type( $post_type );
		}

		return $rules;
	}

	/**
	 * Get default rule for a specific post type
	 */
	private function get_default_rule_for_post_type( $post_type ) {
		$path_template = '/{slug}';
		$archive_paths = array();

		if ( $post_type === 'post' ) {
			$path_template = '/blog/{slug}';
			$archive_paths = array( '/blog', '/' );
		} elseif ( $post_type === 'page' ) {
			$path_template = '/{slug}';
			$archive_paths = array( '/' );
		} else {
			$post_type_obj = get_post_type_object( $post_type );
			$slug = $post_type_obj && $post_type_obj->rewrite ? $post_type_obj->rewrite['slug'] : $post_type;
			$path_template = '/' . $slug . '/{slug}';
			$archive_paths = array( '/' . $slug );
		}

		return array(
			'post_type'          => $post_type,
			'path_template'      => $path_template,
			'auto_revalidate'    => true,
			'revalidate_archives' => true,
			'archive_paths'      => $archive_paths,
		);
	}

	/**
	 * Get public post types
	 */
	private function get_public_post_types() {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'names'
		);

		$excluded = array( 'attachment', 'nav_menu_item', 'wp_block', 'wp_template', 'wp_template_part', 'wp_navigation' );
		return array_values( array_diff( $post_types, $excluded ) );
	}

	/**
	 * Generate paths for a post based on configured rules
	 */
	public function generate_paths_for_post( $post ) {
		$settings = $this->get_settings();
		$post_type = $post->post_type;

		// Check if post type has rules configured
		if ( ! isset( $settings['rules'][ $post_type ] ) ) {
			return array();
		}

		$rule = $settings['rules'][ $post_type ];
		$paths = array();

		// Generate main post path from template
		$post_path = str_replace( '{slug}', $post->post_name, $rule['path_template'] );
		$paths[] = $post_path;

		// Add archive paths if configured
		if ( $rule['revalidate_archives'] && ! empty( $rule['archive_paths'] ) ) {
			$paths = array_merge( $paths, $rule['archive_paths'] );
		}

		return array_unique( $paths );
	}

	/**
	 * Check if auto-revalidation is enabled for a post type
	 */
	public function is_auto_revalidate_enabled( $post_type ) {
		$settings = $this->get_settings();

		// Check global setting
		if ( ! $settings['global_enabled'] ) {
			return false;
		}

		// Check if post type is excluded
		if ( in_array( $post_type, $settings['excluded_post_types'], true ) ) {
			return false;
		}

		// Check post type specific setting
		if ( isset( $settings['rules'][ $post_type ] ) ) {
			return $settings['rules'][ $post_type ]['auto_revalidate'];
		}

		return true;
	}

	public function trigger_revalidation( $paths ) {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );
		$secret = flatwp()->get_setting( 'api_secret' );

		if ( empty( $nextjs_url ) || empty( $secret ) ) {
			$error = new WP_Error( 'missing_config', __( 'Next.js URL or secret not configured', 'flatwp-wordpress-connector' ) );
			$this->log_activity(
				'revalidation_failed',
				'error',
				'ISR revalidation failed: Next.js URL or secret not configured'
			);

			// Log to new logger
			if ( flatwp()->logger ) {
				flatwp()->logger->log_error(
					'Revalidation failed: Next.js URL or secret not configured',
					array( 'paths' => $paths )
				);
			}

			return $error;
		}

		// Validate Next.js URL
		$url_validation = $this->security->validate_nextjs_url( $nextjs_url, true );
		if ( is_wp_error( $url_validation ) ) {
			$this->log_activity(
				'revalidation_failed',
				'error',
				sprintf( 'Invalid Next.js URL: %s', $url_validation->get_error_message() )
			);
			return $url_validation;
		}

		// Check rate limit
		$client_ip = $this->security->get_client_ip();
		$rate_check = $this->security->check_rate_limit( $client_ip, 'revalidation_webhook' );
		if ( is_wp_error( $rate_check ) ) {
			$this->log_activity(
				'revalidation_failed',
				'error',
				sprintf( 'Rate limit exceeded for IP %s', $client_ip )
			);
			return $rate_check;
		}

		$revalidate_url = trailingslashit( $nextjs_url ) . 'api/revalidate';

		// Log revalidation attempt with paths
		$paths_display = is_array( $paths ) ? implode( ', ', $paths ) : $paths;
		$this->log_activity(
			'revalidation_triggered',
			'info',
			sprintf( 'ISR revalidation triggered for %d path(s): %s', count( (array) $paths ), $paths_display )
		);

		// Prepare simple payload - match working implementation exactly
		$payload = wp_json_encode( array(
			'secret' => $secret,
			'paths'  => $paths,
		) );

		// Generate HMAC signature
		$signature = $this->security->generate_webhook_signature( $payload, $secret );

		// Measure request duration
		$start_time = microtime( true );

		$response = wp_remote_post(
			$revalidate_url,
			array(
				'body'    => $payload,
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-FlatWP-Signature' => $signature,
					'X-FlatWP-Timestamp' => (string) time(),
				),
				'timeout' => flatwp()->get_setting( 'webhook_timeout', 30 ),
			)
		);

		$duration = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			$error_message = sprintf( 'ISR webhook failed after %dms: %s', $duration, $response->get_error_message() );
			$this->log_activity(
				'revalidation_failed',
				'error',
				$error_message
			);

			// Log to new logger
			if ( flatwp()->logger ) {
				flatwp()->logger->log_webhook(
					$error_message,
					$paths,
					array(
						'error' => $response->get_error_message(),
						'code' => 0,
					),
					$duration
				);
			}

			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Parse response for success/failure details
		if ( $response_code === 200 ) {
			$success_message = sprintf( 'ISR revalidation successful (%dms) - %d path(s) marked for rebuild on next request', $duration, count( (array) $paths ) );
			$this->log_activity(
				'revalidation_success',
				'success',
				$success_message
			);

			// Log to new logger
			if ( flatwp()->logger ) {
				flatwp()->logger->log_webhook(
					$success_message,
					$paths,
					array(
						'code' => $response_code,
						'body' => json_decode( $response_body, true ),
					),
					$duration
				);
			}

			update_option( 'flatwp_cache_last_cleared', current_time( 'timestamp' ) );

			// Track last successful webhook for health monitoring
			update_option( 'flatwp_last_webhook_success', current_time( 'timestamp' ) );

			// Track stats for dashboard
			$this->increment_daily_revalidations( count( (array) $paths ) );

			return true;
		} else {
			$error_detail = $response_body ? substr( $response_body, 0, 100 ) : 'Unknown error';
			$warning_message = sprintf( 'ISR webhook returned HTTP %d after %dms: %s', $response_code, $duration, $error_detail );
			$this->log_activity(
				'revalidation_failed',
				'warning',
				$warning_message
			);

			// Log to new logger
			if ( flatwp()->logger ) {
				flatwp()->logger->log_webhook(
					$warning_message,
					$paths,
					array(
						'code' => $response_code,
						'body' => $response_body,
					),
					$duration
				);
			}

			return new WP_Error( 'revalidation_failed', sprintf( 'HTTP %d: %s', $response_code, $error_detail ) );
		}
	}

	public function handle_post_save( $post_id, $post ) {
		// Check if webhooks are enabled
		if ( ! flatwp()->get_setting( 'webhook_enabled', true ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if auto-revalidation is enabled for this post type
		if ( ! $this->is_auto_revalidate_enabled( $post->post_type ) ) {
			return;
		}

		if ( $post->post_status === 'publish' ) {
			// Generate paths based on configured rules
			$paths = $this->generate_paths_for_post( $post );

			if ( empty( $paths ) ) {
				return;
			}

			$result = $this->trigger_revalidation( $paths );

			if ( is_wp_error( $result ) ) {
				$this->log_activity(
					'revalidation_failed',
					'error',
					sprintf( 'Failed to revalidate %s: %s', $post->post_title, $result->get_error_message() )
				);
			} else {
				$this->log_activity(
					'revalidation_triggered',
					'success',
					sprintf( '%s "%s" updated - cache revalidated (%d paths)', ucfirst( $post->post_type ), $post->post_title, count( $paths ) )
				);
			}

			// Trigger webhooks for integrations (Yoast SEO, ACF, etc)
			if ( class_exists( 'FlatWP_Webhook_Integration' ) ) {
				FlatWP_Webhook_Integration::trigger_from_revalidation( $paths, $post );
			}
		}
	}

	public function handle_status_change( $new_status, $old_status, $post ) {
		// Check if webhooks are enabled
		if ( ! flatwp()->get_setting( 'webhook_enabled', true ) ) {
			return;
		}

		// Check if auto-revalidation is enabled for this post type
		if ( ! $this->is_auto_revalidate_enabled( $post->post_type ) ) {
			return;
		}

		if ( $new_status === 'publish' && $old_status !== 'publish' ) {
			// Generate paths based on configured rules
			$paths = $this->generate_paths_for_post( $post );

			if ( empty( $paths ) ) {
				return;
			}

			$result = $this->trigger_revalidation( $paths );

			if ( is_wp_error( $result ) ) {
				$this->log_activity(
					'revalidation_failed',
					'error',
					sprintf( 'Failed to revalidate published %s: %s', $post->post_title, $result->get_error_message() )
				);
			} else {
				$this->log_activity(
					'revalidation_triggered',
					'success',
					sprintf( '%s "%s" published - cache revalidated (%d paths)', ucfirst( $post->post_type ), $post->post_title, count( $paths ) )
				);
			}

			// Trigger webhooks for integrations (Yoast SEO, ACF, etc)
			if ( class_exists( 'FlatWP_Webhook_Integration' ) ) {
				FlatWP_Webhook_Integration::trigger_from_revalidation( $paths, $post );
			}
		}
	}

	/**
	 * Log activity
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

		// Also log to dashboard widget activity
		if ( class_exists( 'FlatWP_Dashboard_Widget' ) ) {
			FlatWP_Dashboard_Widget::log_activity( $message );
		}
	}

	/**
	 * Increment daily revalidations counter
	 *
	 * @param int $count Number of paths revalidated.
	 */
	private function increment_daily_revalidations( $count = 1 ) {
		$today = current_time( 'Y-m-d' );
		$daily_stats = get_transient( 'flatwp_daily_stats' );

		if ( ! is_array( $daily_stats ) || ! isset( $daily_stats['date'] ) || $daily_stats['date'] !== $today ) {
			// Reset for new day
			$daily_stats = array(
				'date'          => $today,
				'revalidations' => 0,
			);
		}

		$daily_stats['revalidations'] += $count;

		set_transient( 'flatwp_daily_stats', $daily_stats, DAY_IN_SECONDS );

		// Also update the simple counter for backward compatibility
		$revalidations_today = get_transient( 'flatwp_revalidations_today' );
		if ( false === $revalidations_today ) {
			$revalidations_today = 0;
		}
		set_transient( 'flatwp_revalidations_today', $revalidations_today + $count, DAY_IN_SECONDS );
	}

	/**
	 * Test webhook connection
	 *
	 * Uses blocking mode to test the connection and return actual response.
	 * Useful for validating Next.js configuration during setup.
	 *
	 * @return array|WP_Error Response array with success/error details
	 */
	public function test_webhook_connection() {
		// Force blocking mode for test
		add_filter( 'flatwp_webhook_test_mode', '__return_true' );

		// Test with simple payload (just homepage)
		$result = $this->trigger_revalidation( array( '/' ) );

		// Remove filter
		remove_filter( 'flatwp_webhook_test_mode', '__return_true' );

		// Parse result for UI display
		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'error' => $result->get_error_message(),
				'code' => $result->get_error_code(),
			);
		}

		return array(
			'success' => true,
			'message' => 'Connection successful! Webhook endpoint is responding.',
		);
	}
}
