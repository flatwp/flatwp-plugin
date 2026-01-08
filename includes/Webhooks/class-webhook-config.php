<?php
/**
 * FlatWP Webhook Configuration Class
 *
 * Manages webhook configuration including targets, filters, payloads, and metrics.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Config {

	/**
	 * Get webhook configuration structure
	 */
	public static function get_schema() {
		return array(
			'version'           => '2.0',
			'targets'           => array(),
			'triggers'          => array(),
			'payload_config'    => array(),
			'metrics_enabled'   => true,
			'plugins'           => array(),
		);
	}

	/**
	 * Get default webhook configuration
	 */
	public static function get_defaults() {
		return array(
			'version'        => '2.0',
			'targets'        => array(
				array(
					'id'       => 'primary',
					'name'     => 'Primary Next.js Site',
					'url'      => '',
					'enabled'  => true,
					'priority' => 1,
					'timeout'  => 15,
					'retries'  => 3,
					'headers'  => array(),
				),
			),
			'triggers'       => array(
				'post'     => array(
					'enabled'  => true,
					'post_types' => array( 'post', 'page' ),
					'statuses' => array( 'publish', 'draft', 'pending' ),
				),
				'term'     => array(
					'enabled'  => false,
					'taxonomies' => array(),
				),
			),
			'payload_config' => array(
				'include_post_meta'      => false,
				'include_featured_image' => false,
				'include_excerpt'        => true,
				'custom_fields'          => array(),
				'min_payload_size'       => false, // Send minimal payload
			),
			'metrics_enabled' => true,
			'plugins'         => array(), // Registered webhook plugins
		);
	}

	/**
	 * Validate webhook target configuration
	 */
	public static function validate_target( $target ) {
		$errors = array();

		if ( empty( $target['id'] ) ) {
			$errors[] = 'Target ID is required';
		}

		if ( empty( $target['name'] ) ) {
			$errors[] = 'Target name is required';
		}

		if ( empty( $target['url'] ) ) {
			$errors[] = 'Target URL is required';
		} else {
			// URL validation - allow Docker internal hostnames (e.g., http://frontend:3010)
			$url = $target['url'];
			$is_valid = filter_var( $url, FILTER_VALIDATE_URL );

			// Also accept Docker/internal URLs like http://frontend:3010 or http://service-name/path
			if ( ! $is_valid ) {
				$is_valid = preg_match( '#^https?://[a-zA-Z0-9_-]+(?::\d+)?(?:/.*)?$#', $url );
			}

			if ( ! $is_valid ) {
				$errors[] = 'Invalid target URL format';
			}
		}

		if ( ! isset( $target['priority'] ) || ! is_numeric( $target['priority'] ) ) {
			$errors[] = 'Priority must be numeric';
		}

		if ( ! isset( $target['timeout'] ) || ! is_numeric( $target['timeout'] ) ) {
			$errors[] = 'Timeout must be numeric';
		} elseif ( $target['timeout'] < 1 || $target['timeout'] > 300 ) {
			$errors[] = 'Timeout must be between 1 and 300 seconds';
		}

		if ( ! isset( $target['retries'] ) || ! is_numeric( $target['retries'] ) ) {
			$errors[] = 'Retries must be numeric';
		} elseif ( $target['retries'] < 0 || $target['retries'] > 10 ) {
			$errors[] = 'Retries must be between 0 and 10';
		}

		return empty( $errors ) ? true : $errors;
	}

	/**
	 * Validate trigger configuration
	 */
	public static function validate_triggers( $triggers ) {
		$errors = array();

		if ( isset( $triggers['post'] ) ) {
			if ( ! is_array( $triggers['post']['post_types'] ) || empty( $triggers['post']['post_types'] ) ) {
				$errors[] = 'Post triggers must include at least one post type';
			}

			if ( ! is_array( $triggers['post']['statuses'] ) || empty( $triggers['post']['statuses'] ) ) {
				$errors[] = 'Post triggers must include at least one status';
			}
		}

		return empty( $errors ) ? true : $errors;
	}

	/**
	 * Get current configuration
	 */
	public static function get_config() {
		$saved = get_option( 'flatwp_webhook_config', array() );
		return wp_parse_args( $saved, self::get_defaults() );
	}

	/**
	 * Update configuration
	 */
	public static function update_config( $config ) {
		// Validate all targets
		if ( isset( $config['targets'] ) && is_array( $config['targets'] ) ) {
			foreach ( $config['targets'] as $target ) {
				$validation = self::validate_target( $target );
				if ( $validation !== true ) {
					return new WP_Error( 'invalid_target', implode( ', ', $validation ) );
				}
			}
		}

		// Validate triggers
		if ( isset( $config['triggers'] ) ) {
			$validation = self::validate_triggers( $config['triggers'] );
			if ( $validation !== true ) {
				return new WP_Error( 'invalid_triggers', implode( ', ', $validation ) );
			}
		}

		// Merge with defaults and save
		$current = self::get_config();
		$updated = wp_parse_args( $config, $current );

		update_option( 'flatwp_webhook_config', $updated );

		return $updated;
	}

	/**
	 * Get enabled webhook targets (sorted by priority)
	 */
	public static function get_enabled_targets() {
		$config = self::get_config();
		$targets = isset( $config['targets'] ) ? $config['targets'] : array();

		$enabled = array_filter(
			$targets,
			function ( $target ) {
				return ! empty( $target['enabled'] );
			}
		);

		// Sort by priority
		usort(
			$enabled,
			function ( $a, $b ) {
				return $a['priority'] - $b['priority'];
			}
		);

		return $enabled;
	}

	/**
	 * Check if trigger is enabled
	 */
	public static function is_trigger_enabled( $trigger_type = 'post', $post_type = null, $status = null ) {
		$config = self::get_config();

		// Check if trigger type is enabled
		if ( ! isset( $config['triggers'][ $trigger_type ] ) || empty( $config['triggers'][ $trigger_type ]['enabled'] ) ) {
			return false;
		}

		// Additional checks for post trigger
		if ( $trigger_type === 'post' ) {
			$post_types = $config['triggers']['post']['post_types'] ?? array();
			$statuses = $config['triggers']['post']['statuses'] ?? array();

			if ( $post_type && ! in_array( $post_type, $post_types, true ) ) {
				return false;
			}

			if ( $status && ! in_array( $status, $statuses, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get payload configuration for a post
	 */
	public static function get_payload_config( $post = null ) {
		$config = self::get_config();
		$payload_config = $config['payload_config'] ?? array();

		// Allow plugins to modify payload config
		$payload_config = apply_filters( 'flatwp_webhook_payload_config', $payload_config, $post );

		return $payload_config;
	}
}
