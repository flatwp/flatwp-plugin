<?php
/**
 * FlatWP Security Class
 *
 * Handles security features including rate limiting, input validation,
 * webhook authentication, and security logging.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Security {

	/**
	 * Rate limit configuration
	 */
	const RATE_LIMIT_WINDOW = 60; // 1 minute
	const RATE_LIMIT_MAX_REQUESTS = 60; // Max requests per window
	const RATE_LIMIT_LOCKOUT_DURATION = 300; // 5 minutes lockout

	/**
	 * Failed authentication tracking
	 */
	const MAX_FAILED_ATTEMPTS = 5;
	const FAILED_ATTEMPT_WINDOW = 300; // 5 minutes

	/**
	 * Secret strength requirements
	 */
	const MIN_SECRET_LENGTH = 32;
	const RECOMMENDED_SECRET_LENGTH = 64;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Clean up expired rate limit entries daily
		add_action( 'flatwp_daily_cleanup', array( $this, 'cleanup_rate_limits' ) );

		// Schedule daily cleanup if not already scheduled
		if ( ! wp_next_scheduled( 'flatwp_daily_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'flatwp_daily_cleanup' );
		}
	}

	/**
	 * Check rate limit for a given identifier
	 *
	 * @param string $identifier Unique identifier (IP, user ID, etc.).
	 * @param string $action Action being rate limited.
	 * @return bool|WP_Error True if allowed, WP_Error if rate limited.
	 */
	public function check_rate_limit( $identifier, $action = 'default' ) {
		$key = 'flatwp_rate_limit_' . md5( $identifier . $action );
		$requests = get_transient( $key );

		// Check if locked out
		$lockout_key = $key . '_lockout';
		if ( get_transient( $lockout_key ) ) {
			$this->log_security_event(
				'rate_limit_lockout',
				sprintf( 'Rate limit lockout active for %s (%s)', $identifier, $action ),
				array(
					'identifier' => $identifier,
					'action'     => $action,
				)
			);

			return new WP_Error(
				'rate_limited',
				__( 'Too many requests. Please try again later.', 'flatwp-wordpress-connector' ),
				array( 'status' => 429 )
			);
		}

		// Initialize or increment request count
		if ( false === $requests ) {
			set_transient( $key, 1, self::RATE_LIMIT_WINDOW );
			return true;
		}

		$requests++;

		// Check if limit exceeded
		if ( $requests > self::RATE_LIMIT_MAX_REQUESTS ) {
			// Set lockout
			set_transient( $lockout_key, true, self::RATE_LIMIT_LOCKOUT_DURATION );

			$this->log_security_event(
				'rate_limit_exceeded',
				sprintf( 'Rate limit exceeded for %s (%s)', $identifier, $action ),
				array(
					'identifier'     => $identifier,
					'action'         => $action,
					'request_count'  => $requests,
				)
			);

			return new WP_Error(
				'rate_limited',
				__( 'Rate limit exceeded. Please try again later.', 'flatwp-wordpress-connector' ),
				array( 'status' => 429 )
			);
		}

		// Update request count
		set_transient( $key, $requests, self::RATE_LIMIT_WINDOW );
		return true;
	}

	/**
	 * Validate Next.js URL
	 *
	 * @param string $url URL to validate.
	 * @param bool   $require_https Whether to require HTTPS in production.
	 * @return bool|WP_Error True if valid, WP_Error otherwise.
	 */
	public function validate_nextjs_url( $url, $require_https = true ) {
		// Sanitize URL
		$url = esc_url_raw( $url );

		if ( empty( $url ) ) {
			return new WP_Error(
				'invalid_url',
				__( 'Next.js URL cannot be empty', 'flatwp-wordpress-connector' )
			);
		}

		// Validate URL format
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error(
				'invalid_url',
				__( 'Invalid URL format', 'flatwp-wordpress-connector' )
			);
		}

		// Parse URL
		$parsed = wp_parse_url( $url );

		// Check scheme
		if ( ! isset( $parsed['scheme'] ) || ! in_array( $parsed['scheme'], array( 'http', 'https' ), true ) ) {
			return new WP_Error(
				'invalid_scheme',
				__( 'URL must use HTTP or HTTPS', 'flatwp-wordpress-connector' )
			);
		}

		// Require HTTPS in production
		if ( $require_https && ! $this->is_development_environment() && $parsed['scheme'] !== 'https' ) {
			return new WP_Error(
				'https_required',
				__( 'HTTPS is required in production environments', 'flatwp-wordpress-connector' )
			);
		}

		// Check for localhost/development URLs in production
		if ( ! $this->is_development_environment() ) {
			$host = isset( $parsed['host'] ) ? $parsed['host'] : '';
			$dev_patterns = array( 'localhost', '127.0.0.1', '.local', '.test', '.dev' );

			foreach ( $dev_patterns as $pattern ) {
				if ( strpos( $host, $pattern ) !== false ) {
					return new WP_Error(
						'dev_url_in_production',
						__( 'Development URLs cannot be used in production', 'flatwp-wordpress-connector' )
					);
				}
			}
		}

		return true;
	}

	/**
	 * Validate API secret strength
	 *
	 * @param string $secret Secret to validate.
	 * @return array Validation result with strength score and messages.
	 */
	public function validate_secret_strength( $secret ) {
		$result = array(
			'valid'    => false,
			'strength' => 'weak',
			'score'    => 0,
			'messages' => array(),
		);

		if ( empty( $secret ) ) {
			$result['messages'][] = __( 'Secret cannot be empty', 'flatwp-wordpress-connector' );
			return $result;
		}

		$length = strlen( $secret );
		$score = 0;

		// Length check
		if ( $length < self::MIN_SECRET_LENGTH ) {
			$result['messages'][] = sprintf(
				/* translators: %d: minimum number of characters required */
				__( 'Secret must be at least %d characters long', 'flatwp-wordpress-connector' ),
				self::MIN_SECRET_LENGTH
			);
			return $result;
		}

		// Length scoring
		if ( $length >= self::RECOMMENDED_SECRET_LENGTH ) {
			$score += 40;
		} elseif ( $length >= self::MIN_SECRET_LENGTH ) {
			$score += 20;
		}

		// Complexity checks
		$has_lowercase = preg_match( '/[a-z]/', $secret );
		$has_uppercase = preg_match( '/[A-Z]/', $secret );
		$has_numbers = preg_match( '/[0-9]/', $secret );
		$has_special = preg_match( '/[^a-zA-Z0-9]/', $secret );

		if ( $has_lowercase ) {
			$score += 15;
		}
		if ( $has_uppercase ) {
			$score += 15;
		}
		if ( $has_numbers ) {
			$score += 15;
		}
		if ( $has_special ) {
			$score += 15;
		}

		// Determine strength
		$result['valid'] = true;
		$result['score'] = $score;

		if ( $score >= 80 ) {
			$result['strength'] = 'strong';
			$result['messages'][] = __( 'Strong secret', 'flatwp-wordpress-connector' );
		} elseif ( $score >= 50 ) {
			$result['strength'] = 'medium';
			$result['messages'][] = __( 'Medium strength secret', 'flatwp-wordpress-connector' );
			if ( $length < self::RECOMMENDED_SECRET_LENGTH ) {
				$result['messages'][] = sprintf(
					/* translators: %d: recommended number of characters */
					__( 'Consider using at least %d characters for better security', 'flatwp-wordpress-connector' ),
					self::RECOMMENDED_SECRET_LENGTH
				);
			}
		} else {
			$result['strength'] = 'weak';
			$result['messages'][] = __( 'Weak secret - consider regenerating', 'flatwp-wordpress-connector' );

			if ( ! $has_uppercase || ! $has_lowercase ) {
				$result['messages'][] = __( 'Include both uppercase and lowercase letters', 'flatwp-wordpress-connector' );
			}
			if ( ! $has_numbers ) {
				$result['messages'][] = __( 'Include numbers', 'flatwp-wordpress-connector' );
			}
			if ( ! $has_special ) {
				$result['messages'][] = __( 'Include special characters', 'flatwp-wordpress-connector' );
			}
		}

		return $result;
	}

	/**
	 * Generate webhook signature using HMAC
	 *
	 * @param string $payload Payload to sign.
	 * @param string $secret Secret key.
	 * @return string HMAC signature.
	 */
	public function generate_webhook_signature( $payload, $secret ) {
		return hash_hmac( 'sha256', $payload, $secret );
	}

	/**
	 * Verify webhook signature
	 *
	 * @param string $payload Payload to verify.
	 * @param string $signature Provided signature.
	 * @param string $secret Secret key.
	 * @return bool True if valid, false otherwise.
	 */
	public function verify_webhook_signature( $payload, $signature, $secret ) {
		$expected_signature = $this->generate_webhook_signature( $payload, $secret );
		return hash_equals( $expected_signature, $signature );
	}

	/**
	 * Track failed authentication attempt
	 *
	 * @param string $identifier Unique identifier.
	 * @param string $context Context of the failure.
	 * @return bool|WP_Error True if allowed, WP_Error if locked out.
	 */
	public function track_failed_auth_attempt( $identifier, $context = 'webhook' ) {
		$key = 'flatwp_failed_auth_' . md5( $identifier );
		$attempts = get_transient( $key );

		if ( false === $attempts ) {
			$attempts = array(
				'count'      => 1,
				'first_fail' => time(),
			);
		} else {
			$attempts['count']++;
		}

		set_transient( $key, $attempts, self::FAILED_ATTEMPT_WINDOW );

		$this->log_security_event(
			'failed_authentication',
			sprintf( 'Failed authentication attempt for %s (%s)', $identifier, $context ),
			array(
				'identifier'  => $identifier,
				'context'     => $context,
				'attempt_num' => $attempts['count'],
			)
		);

		// Check if locked out
		if ( $attempts['count'] >= self::MAX_FAILED_ATTEMPTS ) {
			$lockout_key = $key . '_lockout';
			set_transient( $lockout_key, true, self::FAILED_ATTEMPT_WINDOW );

			$this->log_security_event(
				'authentication_lockout',
				sprintf( 'Authentication lockout triggered for %s after %d failed attempts', $identifier, $attempts['count'] ),
				array(
					'identifier'     => $identifier,
					'context'        => $context,
					'failed_attempts' => $attempts['count'],
				)
			);

			return new WP_Error(
				'locked_out',
				__( 'Too many failed authentication attempts. Please try again later.', 'flatwp-wordpress-connector' ),
				array( 'status' => 429 )
			);
		}

		return true;
	}

	/**
	 * Clear failed authentication attempts for identifier
	 *
	 * @param string $identifier Unique identifier.
	 */
	public function clear_failed_auth_attempts( $identifier ) {
		$key = 'flatwp_failed_auth_' . md5( $identifier );
		delete_transient( $key );
		delete_transient( $key . '_lockout' );
	}

	/**
	 * Check if IP is whitelisted
	 *
	 * @param string $ip IP address to check.
	 * @return bool True if whitelisted or whitelist disabled.
	 */
	public function is_ip_whitelisted( $ip ) {
		$whitelist = flatwp()->get_setting( 'webhook_ip_whitelist', array() );

		// If whitelist is empty or disabled, allow all
		if ( empty( $whitelist ) ) {
			return true;
		}

		// Sanitize IP
		$ip = filter_var( $ip, FILTER_VALIDATE_IP );
		if ( ! $ip ) {
			return false;
		}

		// Check against whitelist
		foreach ( $whitelist as $whitelisted_ip ) {
			// Support CIDR notation
			if ( $this->ip_in_range( $ip, $whitelisted_ip ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if IP is in CIDR range
	 *
	 * @param string $ip IP address.
	 * @param string $range CIDR range.
	 * @return bool True if in range.
	 */
	private function ip_in_range( $ip, $range ) {
		// Direct match
		if ( $ip === $range ) {
			return true;
		}

		// Check CIDR notation
		if ( strpos( $range, '/' ) === false ) {
			return false;
		}

		list( $subnet, $mask ) = explode( '/', $range );

		$ip_long = ip2long( $ip );
		$subnet_long = ip2long( $subnet );
		$mask_long = -1 << ( 32 - (int) $mask );

		return ( $ip_long & $mask_long ) === ( $subnet_long & $mask_long );
	}

	/**
	 * Get client IP address
	 *
	 * @return string Client IP address.
	 */
	public function get_client_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

				// Handle comma-separated IPs (X-Forwarded-For)
				if ( strpos( $ip, ',' ) !== false ) {
					$ips = explode( ',', $ip );
					$ip = trim( $ips[0] );
				}

				// Validate IP
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Check if running in development environment
	 *
	 * @return bool True if development environment.
	 */
	private function is_development_environment() {
		// Check WP_DEBUG
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		// Check WP_ENVIRONMENT_TYPE (WordPress 5.5+)
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$env = wp_get_environment_type();
			return in_array( $env, array( 'local', 'development' ), true );
		}

		return false;
	}

	/**
	 * Log security event
	 *
	 * @param string $event_type Type of security event.
	 * @param string $message Event message.
	 * @param array  $context Additional context.
	 */
	private function log_security_event( $event_type, $message, $context = array() ) {
		$logs = get_option( 'flatwp_security_logs', array() );

		$log_entry = array(
			'id'         => uniqid(),
			'timestamp'  => current_time( 'timestamp' ),
			'event_type' => $event_type,
			'message'    => $message,
			'context'    => $context,
			'ip'         => $this->get_client_ip(),
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		);

		// Add to beginning of array
		array_unshift( $logs, $log_entry );

		// Keep only last 200 entries
		$logs = array_slice( $logs, 0, 200 );

		update_option( 'flatwp_security_logs', $logs );

		// Also log to regular activity log for critical events
		$critical_events = array( 'rate_limit_exceeded', 'authentication_lockout', 'failed_authentication' );
		if ( in_array( $event_type, $critical_events, true ) && class_exists( 'FlatWP_Dashboard_Widget' ) ) {
			FlatWP_Dashboard_Widget::log_activity( $message );
		}
	}

	/**
	 * Get security logs
	 *
	 * @param int    $page Page number.
	 * @param int    $per_page Entries per page.
	 * @param string $event_type Filter by event type.
	 * @return array Paginated security logs.
	 */
	public function get_security_logs( $page = 1, $per_page = 20, $event_type = 'all' ) {
		$logs = get_option( 'flatwp_security_logs', array() );

		// Filter by event type
		if ( $event_type && $event_type !== 'all' ) {
			$logs = array_filter( $logs, function( $log ) use ( $event_type ) {
				return $log['event_type'] === $event_type;
			});
		}

		// Paginate
		$total = count( $logs );
		$offset = ( $page - 1 ) * $per_page;
		$logs = array_slice( $logs, $offset, $per_page );

		return array(
			'logs'  => $logs,
			'total' => $total,
			'pages' => ceil( $total / $per_page ),
		);
	}

	/**
	 * Cleanup expired rate limit entries
	 */
	public function cleanup_rate_limits() {
		// Cleanup is handled automatically by transients
		// This hook is for future manual cleanup if needed
		do_action( 'flatwp_security_cleanup' );
	}

	/**
	 * Generate secure random secret
	 *
	 * @param int $length Length of secret.
	 * @return string Generated secret.
	 */
	public function generate_secure_secret( $length = 64 ) {
		return wp_generate_password( $length, true, true );
	}
}
