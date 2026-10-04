<?php
/**
 * FlatWP Dashboard Widget Class
 *
 * Displays connection stats, cache settings, and activity on WordPress dashboard
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Dashboard_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_widget_styles' ) );
	}

	/**
	 * Register the dashboard widget.
	 */
	public function register_dashboard_widget() {
		wp_add_dashboard_widget(
			'flatwp_dashboard_widget',
			__( '⚡ FlatWP Status', 'flatwp-headless-connector' ),
			array( $this, 'render_dashboard_widget' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue widget styles.
	 */
	public function enqueue_widget_styles( $hook ) {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_add_inline_style( 'wp-admin', $this->get_inline_styles() );
	}

	/**
	 * Get inline CSS for the widget.
	 *
	 * @return string CSS styles.
	 */
	private function get_inline_styles() {
		return '
			.flatwp-dashboard-widget {
				padding: 0 !important;
			}
			.flatwp-dashboard-widget .inside {
				margin: 0 !important;
				padding: 0 !important;
			}
			.flatwp-stat-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
				gap: 1px;
				background: #e0e0e0;
				border: 1px solid #e0e0e0;
				margin: 0;
			}
			.flatwp-stat-item {
				background: #fff;
				padding: 16px;
				text-align: center;
			}
			.flatwp-stat-value {
				font-size: 28px;
				font-weight: 600;
				color: #2271b1;
				margin: 4px 0;
				line-height: 1;
			}
			.flatwp-stat-label {
				font-size: 13px;
				color: #646970;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			.flatwp-status-section {
				padding: 16px;
				border-top: 1px solid #e0e0e0;
			}
			.flatwp-status-section h4 {
				margin: 0 0 12px 0;
				font-size: 14px;
				color: #1d2327;
			}
			.flatwp-status-row {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 8px 0;
				border-bottom: 1px solid #f0f0f0;
			}
			.flatwp-status-row:last-child {
				border-bottom: none;
			}
			.flatwp-status-label {
				font-size: 13px;
				color: #646970;
			}
			.flatwp-status-value {
				font-size: 13px;
				font-weight: 500;
				color: #1d2327;
			}
			.flatwp-status-badge {
				display: inline-block;
				padding: 2px 8px;
				border-radius: 3px;
				font-size: 11px;
				font-weight: 500;
				text-transform: uppercase;
			}
			.flatwp-status-badge.connected {
				background: #d4edda;
				color: #155724;
			}
			.flatwp-status-badge.disconnected {
				background: #f8d7da;
				color: #721c24;
			}
			.flatwp-status-badge.active {
				background: #d1ecf1;
				color: #0c5460;
			}
			.flatwp-activity-list {
				margin: 0;
				padding: 0;
				list-style: none;
			}
			.flatwp-activity-item {
				padding: 8px 0;
				border-bottom: 1px solid #f0f0f0;
				font-size: 13px;
			}
			.flatwp-activity-item:last-child {
				border-bottom: none;
			}
			.flatwp-activity-time {
				color: #646970;
				font-size: 12px;
			}
			.flatwp-activity-message {
				color: #1d2327;
				margin-top: 2px;
			}
			.flatwp-widget-actions {
				padding: 12px 16px;
				background: #f6f7f7;
				border-top: 1px solid #e0e0e0;
				display: flex;
				gap: 8px;
			}
			.flatwp-widget-actions .button {
				flex: 1;
			}
		';
	}

	/**
	 * Render the dashboard widget.
	 */
	public function render_dashboard_widget() {
		$stats = $this->get_stats();
		$connection_status = $this->check_connection_status();
		$recent_activity = $this->get_recent_activity();

		?>
		<div class="flatwp-dashboard-widget">
			<!-- Connection Status -->
			<div class="flatwp-status-section">
				<h4><?php esc_html_e( 'Connection Status', 'flatwp-headless-connector' ); ?></h4>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Next.js Frontend', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-badge <?php echo $connection_status['connected'] ? 'connected' : 'disconnected'; ?>">
						<?php echo $connection_status['connected'] ? esc_html__( 'Connected', 'flatwp-headless-connector' ) : esc_html__( 'Disconnected', 'flatwp-headless-connector' ); ?>
					</span>
				</div>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Frontend URL', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-value">
						<?php
						$nextjs_url = flatwp()->get_setting( 'nextjs_url', __( 'Not configured', 'flatwp-headless-connector' ) );
						echo esc_html( $nextjs_url );
						?>
					</span>
				</div>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Webhooks', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-badge <?php echo flatwp()->get_setting( 'webhook_enabled' ) ? 'active' : 'disconnected'; ?>">
						<?php echo flatwp()->get_setting( 'webhook_enabled' ) ? esc_html__( 'Enabled', 'flatwp-headless-connector' ) : esc_html__( 'Disabled', 'flatwp-headless-connector' ); ?>
					</span>
				</div>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Preview Mode', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-badge <?php echo flatwp()->get_setting( 'enable_preview' ) ? 'active' : 'disconnected'; ?>">
						<?php echo flatwp()->get_setting( 'enable_preview' ) ? esc_html__( 'Enabled', 'flatwp-headless-connector' ) : esc_html__( 'Disabled', 'flatwp-headless-connector' ); ?>
					</span>
				</div>
			</div>

			<!-- Cache Settings -->
			<div class="flatwp-status-section">
				<h4><?php esc_html_e( 'Cache Strategy', 'flatwp-headless-connector' ); ?></h4>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Posts', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-value"><?php echo esc_html( $this->format_cache_strategy( flatwp()->get_setting( 'cache_strategy_posts', 'on-demand' ) ) ); ?></span>
				</div>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Pages', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-value"><?php echo esc_html( $this->format_cache_strategy( flatwp()->get_setting( 'cache_strategy_pages', 'static' ) ) ); ?></span>
				</div>
				<div class="flatwp-status-row">
					<span class="flatwp-status-label"><?php esc_html_e( 'Homepage', 'flatwp-headless-connector' ); ?></span>
					<span class="flatwp-status-value"><?php echo esc_html( $this->format_cache_strategy( flatwp()->get_setting( 'cache_strategy_homepage', '60' ) ) ); ?></span>
				</div>
			</div>

			<!-- Recent Activity -->
			<?php if ( ! empty( $recent_activity ) ) : ?>
			<div class="flatwp-status-section">
				<h4><?php esc_html_e( 'Recent Activity', 'flatwp-headless-connector' ); ?></h4>
				<ul class="flatwp-activity-list">
					<?php foreach ( $recent_activity as $activity ) : ?>
					<li class="flatwp-activity-item">
						<div class="flatwp-activity-time"><?php echo esc_html( $activity['time'] ); ?></div>
						<div class="flatwp-activity-message"><?php echo esc_html( $activity['message'] ); ?></div>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<!-- Quick Actions -->
			<div class="flatwp-widget-actions">
				<button type="button" class="button" onclick="flatwpClearAllCache()">
					<?php esc_html_e( 'Clear All Cache', 'flatwp-headless-connector' ); ?>
				</button>
				<button type="button" class="button" onclick="flatwpRevalidateAll()">
					<?php esc_html_e( 'Revalidate All', 'flatwp-headless-connector' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=flatwp' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Open Dashboard', 'flatwp-headless-connector' ); ?>
				</a>
			</div>
		</div>

		<script>
		function flatwpClearAllCache() {
			if (!confirm('<?php echo esc_js( __( 'This will clear all cached content. Continue?', 'flatwp-headless-connector' ) ); ?>')) {
				return;
			}

			const button = event.target;
			button.disabled = true;
			button.textContent = '<?php echo esc_js( __( 'Clearing...', 'flatwp-headless-connector' ) ); ?>';

			fetch('<?php echo esc_url( rest_url( 'flatwp/v1/cache/clear' ) ); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
				},
				body: JSON.stringify({ type: 'all' })
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('<?php echo esc_js( __( 'Cache cleared successfully!', 'flatwp-headless-connector' ) ); ?>');
					location.reload();
				} else {
					alert('<?php echo esc_js( __( 'Error: ', 'flatwp-headless-connector' ) ); ?>' + (data.message || '<?php echo esc_js( __( 'Unknown error', 'flatwp-headless-connector' ) ); ?>'));
				}
			})
			.catch(error => {
				alert('<?php echo esc_js( __( 'Network error. Please try again.', 'flatwp-headless-connector' ) ); ?>');
			})
			.finally(() => {
				button.disabled = false;
				button.textContent = '<?php echo esc_js( __( 'Clear All Cache', 'flatwp-headless-connector' ) ); ?>';
			});
		}

		function flatwpRevalidateAll() {
			if (!confirm('<?php echo esc_js( __( 'This will trigger revalidation for all content. Continue?', 'flatwp-headless-connector' ) ); ?>')) {
				return;
			}

			const button = event.target;
			button.disabled = true;
			button.textContent = '<?php echo esc_js( __( 'Revalidating...', 'flatwp-headless-connector' ) ); ?>';

			fetch('<?php echo esc_url( rest_url( 'flatwp/v1/revalidate-all' ) ); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
				}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('<?php echo esc_js( __( 'Revalidation triggered successfully!', 'flatwp-headless-connector' ) ); ?>');
					location.reload();
				} else {
					alert('<?php echo esc_js( __( 'Error: ', 'flatwp-headless-connector' ) ); ?>' + (data.message || '<?php echo esc_js( __( 'Unknown error', 'flatwp-headless-connector' ) ); ?>'));
				}
			})
			.catch(error => {
				alert('<?php echo esc_js( __( 'Network error. Please try again.', 'flatwp-headless-connector' ) ); ?>');
			})
			.finally(() => {
				button.disabled = false;
				button.textContent = '<?php echo esc_js( __( 'Revalidate All', 'flatwp-headless-connector' ) ); ?>';
			});
		}
		</script>
		<?php
	}

	/**
	 * Get dashboard stats.
	 *
	 * @return array Stats data.
	 */
	private function get_stats() {
		// Get revalidations count from today
		$revalidations_today = get_transient( 'flatwp_revalidations_today' );
		if ( false === $revalidations_today ) {
			$revalidations_today = 0;
		}

		// Get cache strategy summary
		$cache_strategies = array(
			flatwp()->get_setting( 'cache_strategy_posts', 'on-demand' ),
			flatwp()->get_setting( 'cache_strategy_pages', 'static' ),
			flatwp()->get_setting( 'cache_strategy_categories', '900' ),
		);
		$cache_summary = count( array_unique( $cache_strategies ) ) . ' strategies';

		// Get pending webhooks
		$pending_webhooks = get_option( 'flatwp_pending_webhooks', array() );
		$pending_count = is_array( $pending_webhooks ) ? count( $pending_webhooks ) : 0;

		return array(
			'revalidations_today' => $revalidations_today,
			'cache_hits'          => $cache_summary,
			'pending_webhooks'    => $pending_count,
		);
	}

	/**
	 * Check Next.js connection status.
	 *
	 * @return array Connection status data.
	 */
	private function check_connection_status() {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );

		if ( empty( $nextjs_url ) ) {
			return array(
				'connected' => false,
				'message'   => __( 'Next.js URL not configured', 'flatwp-headless-connector' ),
			);
		}

		// Check if we have a recent successful connection
		$last_connection = get_transient( 'flatwp_last_connection_check' );

		if ( false !== $last_connection ) {
			return array(
				'connected' => true,
				'message'   => __( 'Connected', 'flatwp-headless-connector' ),
			);
		}

		// Try to ping the Next.js site
		$response = wp_remote_get(
			trailingslashit( $nextjs_url ),
			array(
				'timeout' => 5,
				'headers' => array(
					'User-Agent' => 'FlatWP-React/' . FLATWP_VERSION,
				),
			)
		);

		$is_connected = ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;

		if ( $is_connected ) {
			set_transient( 'flatwp_last_connection_check', true, 5 * MINUTE_IN_SECONDS );
		}

		return array(
			'connected' => $is_connected,
			'message'   => $is_connected ? __( 'Connected', 'flatwp-headless-connector' ) : __( 'Connection failed', 'flatwp-headless-connector' ),
		);
	}

	/**
	 * Get recent activity log.
	 *
	 * @return array Activity items.
	 */
	private function get_recent_activity() {
		$activity = get_option( 'flatwp_activity_log', array() );

		// Get the most recent 5 items
		if ( is_array( $activity ) && ! empty( $activity ) ) {
			return array_slice( $activity, 0, 5 );
		}

		return array();
	}

	/**
	 * Format cache strategy for display.
	 *
	 * @param string $strategy Cache strategy value.
	 * @return string Formatted strategy.
	 */
	private function format_cache_strategy( $strategy ) {
		if ( 'on-demand' === $strategy ) {
			return __( 'On-Demand ISR', 'flatwp-headless-connector' );
		}

		if ( 'static' === $strategy ) {
			return __( 'Static', 'flatwp-headless-connector' );
		}

		if ( is_numeric( $strategy ) ) {
			$seconds = intval( $strategy );
			if ( $seconds < 60 ) {
				/* translators: %d: number of seconds */
				return sprintf( __( '%d seconds', 'flatwp-headless-connector' ), $seconds );
			}
			$minutes = floor( $seconds / 60 );
			/* translators: %d: number of minutes */
			return sprintf( __( '%d minutes', 'flatwp-headless-connector' ), $minutes );
		}

		return ucfirst( $strategy );
	}

	/**
	 * Log activity.
	 *
	 * @param string $message Activity message.
	 */
	public static function log_activity( $message ) {
		$activity = get_option( 'flatwp_activity_log', array() );

		if ( ! is_array( $activity ) ) {
			$activity = array();
		}

		// Add new activity to the beginning
		array_unshift(
			$activity,
			array(
				'time'    => current_time( 'mysql' ),
				'message' => $message,
			)
		);

		// Keep only the last 50 items
		$activity = array_slice( $activity, 0, 50 );

		update_option( 'flatwp_activity_log', $activity );
	}
}
