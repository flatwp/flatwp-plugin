<?php
/**
 * FlatWP Frontend Control Class
 *
 * Controls WordPress frontend visibility for headless setups.
 * Provides options to redirect, password protect, or disable the WordPress theme frontend.
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Control Class
 */
class FlatWP_Frontend_Control {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Hook early into template loading
		add_action( 'template_redirect', array( $this, 'handle_frontend_request' ), 1 );
	}

	/**
	 * Handle frontend requests based on settings
	 */
	public function handle_frontend_request() {
		// Skip if we're in admin area
		if ( is_admin() ) {
			return;
		}

		// Get frontend behavior setting
		$behavior = flatwp()->get_setting( 'frontend_behavior', 'normal' );

		// If normal mode, do nothing
		if ( $behavior === 'normal' ) {
			return;
		}

		// Check for exceptions that should always be allowed
		if ( $this->is_exception_request() ) {
			return;
		}

		// Handle based on selected behavior
		switch ( $behavior ) {
			case 'redirect':
				$this->redirect_to_nextjs();
				break;

			case 'password':
				$this->require_password();
				break;

			case 'disable':
				$this->show_headless_page();
				break;
		}
	}

	/**
	 * Check if current request should be exempted from frontend control
	 *
	 * @return bool True if request should be allowed
	 */
	private function is_exception_request() {
		// Always allow REST API requests
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		// Allow GraphQL endpoint
		if ( $this->is_graphql_request() ) {
			return true;
		}

		// Allow preview URLs
		if ( $this->is_preview_request() ) {
			return true;
		}

		// Allow WordPress login and registration pages
		if ( $this->is_login_page() ) {
			return true;
		}

		// In password mode, allow logged-in users
		$behavior = flatwp()->get_setting( 'frontend_behavior', 'normal' );
		if ( $behavior === 'password' && is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if request is for GraphQL endpoint
	 *
	 * @return bool
	 */
	private function is_graphql_request() {
		// WPGraphQL endpoint check
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			if ( strpos( $request_uri, '/graphql' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if request is a preview request
	 *
	 * @return bool
	 */
	private function is_preview_request() {
		// Check for preview query parameter
		if ( isset( $_GET['preview'] ) || isset( $_GET['preview_id'] ) ) {
			return true;
		}

		// Check for FlatWP preview token
		if ( isset( $_GET['flatwp_preview_token'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if request is for login/registration pages
	 *
	 * @return bool
	 */
	private function is_login_page() {
		global $pagenow;

		$login_pages = array( 'wp-login.php', 'wp-register.php' );

		return in_array( $pagenow, $login_pages, true );
	}

	/**
	 * Redirect to Next.js site
	 */
	private function redirect_to_nextjs() {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url' );

		// Validate Next.js URL is configured
		if ( empty( $nextjs_url ) ) {
			wp_die(
				esc_html__( 'WordPress frontend is disabled. Next.js URL not configured.', 'flatwp-wordpress-connector' ),
				esc_html__( 'Frontend Disabled', 'flatwp-wordpress-connector' ),
				array( 'response' => 503 )
			);
		}

		// Validate URL format to prevent open redirect
		if ( ! filter_var( $nextjs_url, FILTER_VALIDATE_URL ) ) {
			wp_die(
				esc_html__( 'Invalid Next.js URL configuration.', 'flatwp-wordpress-connector' ),
				esc_html__( 'Configuration Error', 'flatwp-wordpress-connector' ),
				array( 'response' => 500 )
			);
		}

		// Get current request path
		$request_path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

		// Build redirect URL
		$redirect_url = trailingslashit( $nextjs_url ) . ltrim( $request_path, '/' );

		// Perform redirect
		wp_redirect( $redirect_url, 301 );
		exit;
	}

	/**
	 * Require password (WordPress login) to access frontend
	 */
	private function require_password() {
		// If user is not logged in, redirect to login
		if ( ! is_user_logged_in() ) {
			$login_url = wp_login_url( $this->get_current_url() );
			wp_redirect( $login_url );
			exit;
		}

		// User is logged in, allow access to WordPress frontend
		// No action needed, WordPress will render normally
	}

	/**
	 * Show minimal headless WordPress page
	 */
	private function show_headless_page() {
		$nextjs_url = flatwp()->get_setting( 'nextjs_url', '' );
		$site_name = get_bloginfo( 'name' );

		// Prevent any theme files from loading
		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_footer' );

		// Output minimal HTML page
		header( 'HTTP/1.1 503 Service Unavailable' );
		header( 'Content-Type: text/html; charset=utf-8' );

		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( $site_name ); ?> - Headless WordPress</title>
			<style>
				* {
					margin: 0;
					padding: 0;
					box-sizing: border-box;
				}
				body {
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
					background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
					color: #333;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					padding: 20px;
				}
				.container {
					background: white;
					border-radius: 12px;
					box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
					padding: 40px;
					max-width: 500px;
					text-align: center;
				}
				h1 {
					color: #667eea;
					font-size: 24px;
					margin-bottom: 16px;
					font-weight: 600;
				}
				p {
					color: #666;
					line-height: 1.6;
					margin-bottom: 12px;
				}
				.badge {
					display: inline-block;
					background: #f0f0f0;
					color: #666;
					padding: 6px 12px;
					border-radius: 20px;
					font-size: 14px;
					margin: 20px 0;
				}
				.button {
					display: inline-block;
					background: #667eea;
					color: white;
					padding: 12px 24px;
					border-radius: 6px;
					text-decoration: none;
					font-weight: 500;
					margin-top: 20px;
					transition: background 0.3s ease;
				}
				.button:hover {
					background: #5568d3;
				}
				.admin-link {
					display: block;
					margin-top: 20px;
					color: #667eea;
					text-decoration: none;
					font-size: 14px;
				}
				.admin-link:hover {
					text-decoration: underline;
				}
			</style>
		</head>
		<body>
			<div class="container">
				<h1><?php echo esc_html( $site_name ); ?></h1>
				<div class="badge">🚀 Headless WordPress</div>
				<p>This WordPress installation is running in <strong>headless mode</strong>.</p>
				<p>Content is managed here but displayed on a separate frontend application.</p>

				<?php if ( ! empty( $nextjs_url ) ) : ?>
					<a href="<?php echo esc_url( $nextjs_url ); ?>" class="button">
						Visit Website →
					</a>
				<?php endif; ?>

				<a href="<?php echo esc_url( admin_url() ); ?>" class="admin-link">
					WordPress Admin Dashboard
				</a>
			</div>
		</body>
		</html>
		<?php

		exit;
	}

	/**
	 * Get current URL
	 *
	 * @return string Current page URL
	 */
	private function get_current_url() {
		global $wp;
		return home_url( add_query_arg( array(), $wp->request ) );
	}
}
