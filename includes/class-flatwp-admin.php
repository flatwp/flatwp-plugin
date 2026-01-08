<?php
/**
 * FlatWP Admin Class
 *
 * Handles loading and rendering the React admin app
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_app' ) );
	}

	public function register_admin_pages() {
		add_menu_page(
			__( 'FlatWP', 'flatwp' ),
			__( 'FlatWP', 'flatwp' ),
			'manage_options',
			'flatwp',
			array( $this, 'render_admin_app' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 250" fill="#a7aaad"><path d="M91.94,178.62c-4.28-4.41-6.35-12.18-6.35-23.74V12.38h-52.57v152.79c0,11.36,1.54,21.56,4.56,30.34,2.96,8.6,7.45,16.08,13.35,22.24,5.87,6.14,13.01,10.81,21.22,13.88,8.38,3.13,18.14,4.72,29.03,4.72h49.57v-51.09h-35.87c-11.1,0-18.6-2.17-22.92-6.64h0Z"/><path d="M164.79,135.29v99.93h52.2v-110.21c0-11.36-1.53-21.57-4.52-30.35-2.94-8.59-7.4-16.08-13.25-22.23-5.83-6.14-12.92-10.81-21.08-13.88-8.31-3.14-18.01-4.73-28.82-4.73h-49.68v51.09h35.87c11.1,0,18.6,2.17,22.93,6.64,4.27,4.42,6.35,12.18,6.35,23.74Z"/><path d="M145.91,173.05h-34.79c-8.32,0-15.06-7.7-15.06-17.19l49.85,17.19Z"/><path d="M116.63,173.05v-.03h-4.77c-6.73.01-8.06-8.88-7.97-11.93v-45.1h15.06 19.72c8.32,0,15.06,7.7,15.06,17.19v39.87h-37.11Z"/><polygon points="96.06 155.86 96.06 115.99 105.81 115.99 135.5 115.99 149.47 155.86 153.74 157.38 153.74 173.05 103.05 163.37 96.06 155.86"/></svg>' ),
			30
		);
	}

	public function enqueue_admin_app( $hook ) {
		// Only load on FlatWP admin pages
		if ( strpos( $hook, 'flatwp' ) === false ) {
			return;
		}

		// Check if compiled app exists
		$asset_file = FLATWP_PLUGIN_DIR . 'admin-react/dist/assets/index.js';

		if ( ! file_exists( $asset_file ) ) {
			add_action(
				'admin_notices',
				function() {
					?>
					<div class="notice notice-error">
						<p>
							<strong><?php esc_html_e( 'FlatWP:', 'flatwp' ); ?></strong>
							<?php esc_html_e( 'React admin app not compiled. Please run: cd admin-react && npm install && npm run build', 'flatwp' ); ?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		// Enqueue the built React app
		$asset_url = FLATWP_PLUGIN_URL . 'admin-react/dist/assets/index.js';
		$css_url = FLATWP_PLUGIN_URL . 'admin-react/dist/assets/index.css';

		wp_enqueue_script(
			'flatwp-admin-app',
			$asset_url,
			array(),
			FLATWP_VERSION,
			true
		);

		// Add type="module" attribute for ES modules
		add_filter(
			'script_loader_tag',
			function( $tag, $handle, $src ) {
				if ( 'flatwp-admin-app' === $handle ) {
					$tag = '<script type="module" crossorigin src="' . esc_url( $src ) . '"></script>';
				}
				return $tag;
			},
			10,
			3
		);

		if ( file_exists( FLATWP_PLUGIN_DIR . 'admin-react/dist/assets/index.css' ) ) {
			wp_enqueue_style(
				'flatwp-admin-app',
				$css_url,
				array(),
				FLATWP_VERSION
			);
		}

		// Localize script with data - MUST come before script tag is printed
		wp_localize_script(
			'flatwp-admin-app',
			'flatwpReactAdmin',
			array(
				'apiUrl'    => rest_url( 'flatwp/v1' ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => FLATWP_PLUGIN_URL,
				'version'   => FLATWP_VERSION,
				'siteUrl'   => get_site_url(),
				'adminUrl'  => admin_url(),
			)
		);
	}

	public function render_admin_app() {
		?>
		<div class="wrap">
			<div id="flatwp-root"></div>
		</div>
		<?php
	}
}
