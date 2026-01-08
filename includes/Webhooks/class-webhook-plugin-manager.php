<?php
/**
 * FlatWP Webhook Plugin Manager
 *
 * Manages webhook extensions/plugins for adding custom triggers and payload builders.
 * Allows easy integration with popular plugins like Yoast SEO, Rank Math, etc.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Plugin_Manager {

	/**
	 * Registered webhook plugins
	 */
	private static $plugins = array();

	/**
	 * Register a webhook plugin
	 *
	 * @param string $plugin_id     Unique plugin identifier (e.g., 'yoast-seo')
	 * @param array  $plugin_config Plugin configuration
	 *
	 * Example:
	 * [
	 *   'name'           => 'Yoast SEO',
	 *   'description'    => 'Track SEO changes',
	 *   'triggers'       => ['yoast_seo_meta_updated'],
	 *   'payload_builder'=> callback function,
	 *   'settings'       => [field definitions],
	 * ]
	 */
	public static function register_plugin( $plugin_id, $plugin_config ) {
		// Validate required fields
		if ( empty( $plugin_config['name'] ) || empty( $plugin_config['triggers'] ) ) {
			return new WP_Error(
				'invalid_webhook_plugin',
				'Webhook plugin must have name and triggers'
			);
		}

		self::$plugins[ $plugin_id ] = array(
			'id'              => $plugin_id,
			'name'            => $plugin_config['name'],
			'description'     => $plugin_config['description'] ?? '',
			'version'         => $plugin_config['version'] ?? '1.0',
			'triggers'        => (array) $plugin_config['triggers'],
			'payload_builder' => $plugin_config['payload_builder'] ?? null,
			'settings'        => $plugin_config['settings'] ?? array(),
			'enabled'         => false,
		);

		// Allow other plugins to react to registration
		do_action( 'flatwp_webhook_plugin_registered', $plugin_id, self::$plugins[ $plugin_id ] );

		return true;
	}

	/**
	 * Get registered plugins
	 */
	public static function get_plugins() {
		return self::$plugins;
	}

	/**
	 * Get a specific plugin
	 */
	public static function get_plugin( $plugin_id ) {
		return self::$plugins[ $plugin_id ] ?? null;
	}

	/**
	 * Enable a plugin
	 */
	public static function enable_plugin( $plugin_id ) {
		if ( ! isset( self::$plugins[ $plugin_id ] ) ) {
			return new WP_Error( 'plugin_not_found', "Webhook plugin '{$plugin_id}' not registered" );
		}

		$config = FlatWP_Webhook_Config::get_config();
		$config['plugins'][ $plugin_id ] = array( 'enabled' => true );

		FlatWP_Webhook_Config::update_config( $config );

		self::$plugins[ $plugin_id ]['enabled'] = true;

		do_action( 'flatwp_webhook_plugin_enabled', $plugin_id );

		return true;
	}

	/**
	 * Disable a plugin
	 */
	public static function disable_plugin( $plugin_id ) {
		if ( ! isset( self::$plugins[ $plugin_id ] ) ) {
			return new WP_Error( 'plugin_not_found', "Webhook plugin '{$plugin_id}' not registered" );
		}

		$config = FlatWP_Webhook_Config::get_config();
		unset( $config['plugins'][ $plugin_id ] );

		FlatWP_Webhook_Config::update_config( $config );

		self::$plugins[ $plugin_id ]['enabled'] = false;

		do_action( 'flatwp_webhook_plugin_disabled', $plugin_id );

		return true;
	}

	/**
	 * Check if plugin is enabled
	 */
	public static function is_plugin_enabled( $plugin_id ) {
		return ! empty( self::$plugins[ $plugin_id ]['enabled'] );
	}

	/**
	 * Get enabled plugin triggers
	 */
	public static function get_enabled_triggers() {
		$triggers = array();

		foreach ( self::$plugins as $plugin ) {
			if ( $plugin['enabled'] ) {
				$triggers = array_merge( $triggers, $plugin['triggers'] );
			}
		}

		return array_unique( $triggers );
	}

	/**
	 * Build webhook payload using registered plugins
	 */
	public static function build_payload( $event_type, $event_data = array() ) {
		$payload = $event_data;

		foreach ( self::$plugins as $plugin ) {
			if ( ! $plugin['enabled'] || ! $plugin['payload_builder'] ) {
				continue;
			}

			// If event matches plugin trigger, call builder
			if ( in_array( $event_type, $plugin['triggers'], true ) ) {
				$plugin_payload = call_user_func( $plugin['payload_builder'], $event_data );
				$payload = array_merge( $payload, $plugin_payload );
			}
		}

		return $payload;
	}

	/**
	 * Validate plugin configuration
	 */
	public static function validate_plugin_config( $plugin_id, $plugin_settings ) {
		$plugin = self::get_plugin( $plugin_id );

		if ( ! $plugin ) {
			return new WP_Error( 'plugin_not_found', "Plugin '{$plugin_id}' not found" );
		}

		// Allow plugin to validate its own settings
		$validation = apply_filters( "flatwp_webhook_validate_{$plugin_id}", true, $plugin_settings );

		return $validation;
	}
}
