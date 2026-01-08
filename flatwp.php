<?php
/**
 * Plugin Name: FlatWP - WordPress Connector
 * Plugin URI: https://flatwp.com
 * Description: Modern React-powered admin dashboard for FlatWP with Next.js integration, cache management, and real-time monitoring.
 * Version: 0.8.0
 * Author: FlatWP
 * Author URI: https://flatwp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: flatwp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package FlatWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'FLATWP_VERSION', '0.8.0' );
define( 'FLATWP_PLUGIN_FILE', __FILE__ );
define( 'FLATWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FLATWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FLATWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader if it exists (for any dependencies).
if ( file_exists( FLATWP_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once FLATWP_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Main FlatWP WordPress Connector Class
 *
 * @since 1.0.0
 */
final class FlatWP_Companion {

	/**
	 * Plugin instance.
	 *
	 * @var FlatWP_Companion
	 */
	private static $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var FlatWP_Settings
	 */
	public $settings;

	/**
	 * Revalidation instance.
	 *
	 * @var FlatWP_Revalidation
	 */
	public $revalidation;

	/**
	 * Preview instance.
	 *
	 * @var FlatWP_Preview
	 */
	public $preview;

	/**
	 * Cache Manager instance.
	 *
	 * @var FlatWP_Cache_Manager
	 */
	public $cache_manager;

	/**
	 * REST API instance.
	 *
	 * @var FlatWP_REST_API
	 */
	public $rest_api;

	/**
	 * Admin instance.
	 *
	 * @var FlatWP_Admin
	 */
	public $admin;

	/**
	 * ACF Fields instance.
	 *
	 * @var FlatWP_ACF_Fields
	 */
	public $acf_fields;

	/**
	 * Page Settings instance.
	 *
	 * @var FlatWP_Page_Settings
	 */
	public $page_settings;

	/**
	 * Dashboard Widget instance.
	 *
	 * @var FlatWP_Dashboard_Widget
	 */
	public $dashboard_widget;

	/**
	 * GraphQL Extensions instance.
	 *
	 * @var FlatWP_GraphQL_Extensions
	 */
	public $graphql;

	/**
	 * Frontend Control instance.
	 *
	 * @var FlatWP_Frontend_Control
	 */
	public $frontend_control;

	/**
	 * Security instance.
	 *
	 * @var FlatWP_Security
	 */
	public $security;

	/**
	 * Logger instance.
	 *
	 * @var FlatWP_Logger
	 */
	public $logger;

	/**
	 * Content Logger instance.
	 *
	 * @var FlatWP_Content_Logger
	 */
	public $content_logger;

	/**
	 * Performance Monitor instance.
	 *
	 * @var FlatWP_Performance_Monitor
	 */
	public $performance_monitor;

	/**
	 * Alerts Manager instance.
	 *
	 * @var FlatWP_Alerts_Manager
	 */
	public $alerts_manager;

	/**
	 * Gutenberg Blocks instance.
	 *
	 * @var FlatWP_Gutenberg_Blocks
	 */
	public $gutenberg_blocks;

	/**
	 * Get plugin instance.
	 *
	 * @return FlatWP_Companion
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		// Core classes.
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-security.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-settings.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-logger.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-revalidation.php';

		// Preview system classes (parameter registry and URL resolver).
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-parameter-registry.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-preview-url-resolver.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-preview.php';

		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-cache-manager.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-acf-fields.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-page-settings.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-frontend-control.php';

		// Menu system.
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-menu-locations.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-site-settings.php';

		// Content change logging.
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-content-logger.php';

		// Performance monitoring.
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-performance-monitor.php';
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-alerts-manager.php';

		// GraphQL.
		require_once FLATWP_PLUGIN_DIR . 'graphql/class-flatwp-graphql-extensions.php';
		require_once FLATWP_PLUGIN_DIR . 'graphql/class-flatwp-log-type.php';
		require_once FLATWP_PLUGIN_DIR . 'graphql/class-flatwp-log-resolver.php';
		require_once FLATWP_PLUGIN_DIR . 'graphql/class-flatwp-menus-graphql.php';
		require_once FLATWP_PLUGIN_DIR . 'graphql/class-flatwp-graphql-menu-locations.php';

		// Menu Editor.
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-menu-editor.php';

		// Gutenberg Blocks.
		require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-gutenberg-blocks.php';

		// API.
		require_once FLATWP_PLUGIN_DIR . 'api/class-flatwp-rest-api.php';

		// Admin.
		if ( is_admin() ) {
			require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-admin.php';
			require_once FLATWP_PLUGIN_DIR . 'includes/class-flatwp-dashboard-widget.php';
		}
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Activation and deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Initialize menu system EARLY (must run before 'init' to hook into 'after_setup_theme')
		add_action( 'plugins_loaded', array( $this, 'init_menu_system' ), 1 );

		// Load text domain early.
		add_action( 'init', array( $this, 'load_textdomain' ), 5 );

		// Initialize components.
		add_action( 'init', array( $this, 'init' ), 10 );
	}

	/**
	 * Initialize menu system early.
	 * Menu locations must be registered on 'after_setup_theme',
	 * so we need to init the menu classes before that hook fires.
	 */
	public function init_menu_system() {
		// Initialize menu system early.
		if ( class_exists( 'FlatWP_Menu_Locations' ) ) {
			FlatWP_Menu_Locations::init();
		}
		if ( class_exists( 'FlatWP_Site_Settings' ) ) {
			FlatWP_Site_Settings::init();
		}
		if ( class_exists( 'FlatWP_Menus_GraphQL' ) ) {
			FlatWP_Menus_GraphQL::init();
		}
		if ( class_exists( 'FlatWP_Menu_Editor' ) ) {
			FlatWP_Menu_Editor::init();
		}
		if ( class_exists( 'FlatWP_GraphQL_Menu_Locations' ) ) {
			FlatWP_GraphQL_Menu_Locations::init();
		}
	}

	/**
	 * Initialize plugin components.
	 */
	public function init() {
		// Initialize core classes.
		$this->settings         = new FlatWP_Settings();
		$this->logger           = new FlatWP_Logger();
		$this->security         = new FlatWP_Security();

		// Ensure database tables exist (handles plugin updates without reactivation)
		$this->maybe_upgrade_database();
		$this->revalidation     = new FlatWP_Revalidation();
		$this->preview          = new FlatWP_Preview();
		$this->cache_manager    = new FlatWP_Cache_Manager();

		// Legacy ACF support: Only initialize if ACF Pro is active
		// ACF is maintained for backward compatibility only
		if ( class_exists( 'ACF' ) && function_exists( 'acf_add_local_field_group' ) ) {
			$this->acf_fields = new FlatWP_ACF_Fields();
		}

		$this->page_settings    = new FlatWP_Page_Settings();
		$this->frontend_control = new FlatWP_Frontend_Control();
		$this->graphql          = new FlatWP_GraphQL_Extensions();
		$this->rest_api         = new FlatWP_REST_API();

		// Initialize Native Gutenberg Blocks (for custom FlatWP blocks)
		// Core blocks are exposed via WPGraphQL Content Blocks plugin
		if ( class_exists( 'FlatWP_Gutenberg_Blocks' ) ) {
			$this->gutenberg_blocks = new FlatWP_Gutenberg_Blocks();
		}

		// Menu system already initialized early in init_menu_system()

		// Initialize content change logging.
		$this->content_logger   = new FlatWP_Content_Logger();

		// Initialize performance monitoring.
		$this->performance_monitor = FlatWP_Performance_Monitor::get_instance();
		$this->alerts_manager      = FlatWP_Alerts_Manager::get_instance();

		// Register GraphQL types for logs (requires WPGraphQL to be loaded first).
		add_action(
			'graphql_register_types',
			function () {
				FlatWP_Log_Type::register();
			}
		);

		// Initialize admin interface.
		if ( is_admin() ) {
			$this->admin            = new FlatWP_Admin();
			$this->dashboard_widget = new FlatWP_Dashboard_Widget();
		}

		// Fire init action for extensions.
		do_action( 'flatwp_init', $this );
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
			deactivate_plugins( FLATWP_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'FlatWP - WordPress Connector requires WordPress 6.0 or higher.', 'flatwp' ),
				esc_html__( 'Plugin Activation Error', 'flatwp' ),
				array( 'back_link' => true )
			);
		}

		// Check PHP version.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			deactivate_plugins( FLATWP_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'FlatWP - WordPress Connector requires PHP 7.4 or higher.', 'flatwp' ),
				esc_html__( 'Plugin Activation Error', 'flatwp' ),
				array( 'back_link' => true )
			);
		}

		// Set default options.
		$this->set_default_options();

		// Create logs database table.
		if ( class_exists( 'FlatWP_Logger' ) ) {
			$logger = new FlatWP_Logger();
			$logger->create_logs_table();
		}

		// Create upload directory.
		$upload_dir = wp_upload_dir();
		$flatwp_dir = $upload_dir['basedir'] . '/flatwp';
		if ( ! file_exists( $flatwp_dir ) ) {
			wp_mkdir_p( $flatwp_dir );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();

		// Set activation flag.
		set_transient( 'flatwp_activated', true, 30 );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Clear scheduled events.
		wp_clear_scheduled_hook( 'flatwp_revalidation_retry' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin options.
	 */
	private function set_default_options() {
		$defaults = array(
			// General settings.
			'nextjs_url'                => '',
			'api_secret'                => wp_generate_password( 32, false ),
			'enable_revalidation'       => true,
			'webhook_enabled'           => true,
			'revalidation_delay'        => 0,
			'max_concurrent_webhooks'   => 3,
			'webhook_timeout'           => 30,
			'enable_preview'            => true,
			'preview_url_pattern'       => '/api/preview?secret={secret}&token={token}&id={id}&type={type}&slug={slug}',
			'preview_token_expiration'  => 3600,

			// Frontend control settings.
			'frontend_behavior'         => 'normal',

			// Frontend URL settings.
			'enable_frontend_urls'      => true,
			'frontend_url_patterns'     => array(
				'post' => '/blog/{slug}',
				'page' => '/{slug}',
			),

			// Cache settings.
			'cache_strategy_posts'      => 'on-demand',
			'cache_strategy_pages'      => 'static',
			'cache_strategy_categories' => '900',
			'cache_strategy_tags'       => '900',
			'cache_strategy_homepage'   => '60',

			// Developer settings.
			'enable_debug'              => false,
			'show_admin_notices'        => true,
			'log_level'                 => 'error',
		);

		// Only set defaults if option doesn't exist.
		if ( ! get_option( 'flatwp_settings' ) ) {
			add_option( 'flatwp_settings', $defaults );
		}
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'flatwp',
			false,
			dirname( FLATWP_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Maybe upgrade database tables.
	 * Called on every plugin load to ensure tables exist after updates.
	 */
	public function maybe_upgrade_database() {
		$db_version = get_option( 'flatwp_logs_db_version', '0' );

		// Create or upgrade logs table if needed
		if ( version_compare( $db_version, '1.0', '<' ) ) {
			// Directly call the logger's static table creation method
			global $wpdb;
			$table_name = $wpdb->prefix . 'flatwp_logs';
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

			// Store table version
			update_option( 'flatwp_logs_db_version', '1.0' );
		}
	}

	/**
	 * Get plugin setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_setting( $key, $default = null ) {
		$settings = get_option( 'flatwp_settings', array() );
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Update plugin setting.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public function update_setting( $key, $value ) {
		$settings = get_option( 'flatwp_settings', array() );
		$old_value = isset( $settings[ $key ] ) ? $settings[ $key ] : null;
		$settings[ $key ] = $value;
		$result = update_option( 'flatwp_settings', $settings );

		// Log settings change
		if ( $result && $this->logger ) {
			// Sanitize sensitive keys for logging
			$is_sensitive = in_array( $key, array( 'api_secret', 'preview_token' ), true );
			$display_value = $is_sensitive ? '[REDACTED]' : $value;
			$display_old = $is_sensitive ? '[REDACTED]' : $old_value;

			$this->logger->log_settings(
				sprintf( 'Setting updated: %s', $key ),
				array(
					'key' => $key,
					'old_value' => $display_old,
					'new_value' => $display_value,
					'user_id' => get_current_user_id(),
				)
			);
		}

		return $result;
	}
}

/**
 * Get main plugin instance.
 *
 * @return FlatWP_Companion
 */
function flatwp() {
	return FlatWP_Companion::instance();
}

// Initialize plugin.
flatwp();
