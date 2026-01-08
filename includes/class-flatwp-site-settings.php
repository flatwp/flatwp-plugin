<?php
/**
 * FlatWP Site Settings Manager
 *
 * Handles storage and retrieval of site-wide settings:
 * - Site branding (logo, favicon, description)
 * - Social media links
 * - Contact information
 *
 * Menu assignment is handled by WordPress native menu locations.
 *
 * @package FlatWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Site_Settings {
	/**
	 * Option key for storing all settings
	 */
	const SETTINGS_KEY = 'flatwp_site_settings';

	/**
	 * Initialize settings management
	 */
	public static function init() {
		add_action( 'init', [ self::class, 'register_menu_meta' ] );
		add_action( 'rest_api_init', [ self::class, 'register_rest_routes' ] );
	}

	/**
	 * Register menu item metadata for icons and badges
	 */
	public static function register_menu_meta() {
		register_meta(
			'post',
			'flatwp_icon',
			[
				'type'              => 'string',
				'description'       => 'Lucide icon name for menu item',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_meta(
			'post',
			'flatwp_badge',
			[
				'type'              => 'string',
				'description'       => 'Badge text for menu item',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Register REST API routes
	 */
	public static function register_rest_routes() {
		// Get all settings
		register_rest_route(
			'flatwp/v1',
			'/site-settings',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_settings' ],
				'permission_callback' => '__return_true', // Public read
			]
		);

		// Update settings (admin only)
		register_rest_route(
			'flatwp/v1',
			'/site-settings',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'update_settings' ],
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Get all settings
	 */
	public static function get_settings( $request ) {
		$settings = self::get_option();
		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings
	 */
	public static function update_settings( $request ) {
		$params = $request->get_json_params();

		// Validate settings
		$validated = self::validate_settings( $params );
		if ( is_wp_error( $validated ) ) {
			return rest_ensure_response( $validated );
		}

		// Save to options
		update_option( self::SETTINGS_KEY, $validated );

		// Clear caches
		do_action( 'flatwp_settings_updated', $validated );

		return rest_ensure_response(
			[
				'success'  => true,
				'message'  => 'Settings updated successfully',
				'settings' => $validated,
			]
		);
	}

	/**
	 * Validate settings data
	 */
	private static function validate_settings( $settings ) {
		$validated = [
			'siteDescription' => isset( $settings['siteDescription'] ) ? sanitize_textarea_field( $settings['siteDescription'] ) : '',
			'socialLinks'     => [],
			'contactInfo'     => [],
		];

		// Validate social links
		if ( isset( $settings['socialLinks'] ) && is_array( $settings['socialLinks'] ) ) {
			$validated['socialLinks'] = array_map(
				function( $link ) {
					return [
						'id'       => isset( $link['id'] ) ? sanitize_text_field( $link['id'] ) : wp_generate_uuid4(),
						'label'    => isset( $link['label'] ) ? sanitize_text_field( $link['label'] ) : '',
						'platform' => isset( $link['platform'] ) ? sanitize_title( $link['platform'] ) : '',
						'url'      => isset( $link['url'] ) ? esc_url( $link['url'] ) : '',
						'icon'     => isset( $link['icon'] ) ? sanitize_text_field( $link['icon'] ) : '',
					];
				},
				$settings['socialLinks']
			);
		}

		// Validate contact info
		if ( isset( $settings['contactInfo'] ) && is_array( $settings['contactInfo'] ) ) {
			$validated['contactInfo'] = [
				'email'   => isset( $settings['contactInfo']['email'] ) ? sanitize_email( $settings['contactInfo']['email'] ) : '',
				'phone'   => isset( $settings['contactInfo']['phone'] ) ? sanitize_text_field( $settings['contactInfo']['phone'] ) : '',
				'address' => isset( $settings['contactInfo']['address'] ) ? sanitize_textarea_field( $settings['contactInfo']['address'] ) : '',
			];
		}

		return $validated;
	}

	/**
	 * Get settings from WordPress options
	 */
	public static function get_option() {
		$settings = get_option( self::SETTINGS_KEY );

		if ( ! $settings ) {
			$settings = self::get_default_settings();
		}

		return $settings;
	}

	/**
	 * Get default settings structure
	 */
	private static function get_default_settings() {
		return [
			'siteDescription' => get_bloginfo( 'description' ),
			'socialLinks'     => [],
			'contactInfo'     => [
				'email' => get_option( 'admin_email' ),
				'phone' => '',
				'address' => '',
			],
		];
	}

	/**
	 * Get menu item icon
	 */
	public static function get_menu_item_icon( $menu_item_id ) {
		return get_post_meta( $menu_item_id, 'flatwp_icon', true );
	}

	/**
	 * Set menu item icon
	 */
	public static function set_menu_item_icon( $menu_item_id, $icon ) {
		return update_post_meta( $menu_item_id, 'flatwp_icon', sanitize_text_field( $icon ) );
	}

	/**
	 * Get menu item badge
	 */
	public static function get_menu_item_badge( $menu_item_id ) {
		return get_post_meta( $menu_item_id, 'flatwp_badge', true );
	}

	/**
	 * Set menu item badge
	 */
	public static function set_menu_item_badge( $menu_item_id, $badge ) {
		return update_post_meta( $menu_item_id, 'flatwp_badge', sanitize_text_field( $badge ) );
	}

	/**
	 * Get social links
	 */
	public static function get_social_links() {
		$settings = self::get_option();
		return isset( $settings['socialLinks'] ) ? $settings['socialLinks'] : [];
	}
}
