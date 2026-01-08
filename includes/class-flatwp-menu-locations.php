<?php
/**
 * FlatWP Menu Locations Manager
 *
 * Registers WordPress menu locations for:
 * - Primary navigation
 * - Footer columns (products, resources, company, etc)
 *
 * Uses native WordPress menu location system for simplicity.
 *
 * @package FlatWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Menu_Locations {
	/**
	 * Initialize menu locations
	 */
	public static function init() {
		error_log( 'FlatWP_Menu_Locations::init() called' );
		add_action( 'after_setup_theme', [ self::class, 'add_theme_support' ], 1 );
		add_action( 'after_setup_theme', [ self::class, 'register_menu_locations' ] );
		add_action( 'rest_api_init', [ self::class, 'register_rest_routes' ] );
	}

	/**
	 * Add theme support for menus and widgets
	 * Required for headless/minimal themes that don't declare support
	 */
	public static function add_theme_support() {
		error_log( 'FlatWP_Menu_Locations::add_theme_support() called' );
		// Enable navigation menus
		add_theme_support( 'menus' );

		// Enable widgets (for backward compatibility)
		add_theme_support( 'widgets' );
		error_log( 'Theme support added for menus and widgets' );
	}

	/**
	 * Register all menu locations
	 *
	 * Users see these in WordPress Menus → Manage Locations
	 * and can assign menus via dropdown select
	 */
	public static function register_menu_locations() {
		error_log( 'FlatWP_Menu_Locations::register_menu_locations() called' );
		register_nav_menus(
			[
				'primary-nav'       => __( 'Primary Navigation', 'flatwp-wordpress-connector' ),
				'footer-column-1'   => __( 'Footer - Column 1 (Products)', 'flatwp-wordpress-connector' ),
				'footer-column-2'   => __( 'Footer - Column 2 (Resources)', 'flatwp-wordpress-connector' ),
				'footer-column-3'   => __( 'Footer - Column 3 (Company)', 'flatwp-wordpress-connector' ),
				'footer-column-4'   => __( 'Footer - Column 4', 'flatwp-wordpress-connector' ),
				'mobile-menu'       => __( 'Mobile Menu (Optional)', 'flatwp-wordpress-connector' ),
			]
		);
		error_log( 'Menu locations registered: ' . count( get_registered_nav_menus() ) );
	}

	/**
	 * Get menu object by location
	 *
	 * @param string $location Menu location slug
	 * @return WP_Term|null Menu object or null if no menu assigned
	 */
	public static function get_menu_by_location( $location ) {
		$locations = get_nav_menu_locations();
		$menu_id = isset( $locations[ $location ] ) ? $locations[ $location ] : 0;

		if ( ! $menu_id ) {
			return null;
		}

		return wp_get_nav_menu_object( $menu_id );
	}

	/**
	 * Get menu items for a location
	 *
	 * @param string $location Menu location slug
	 * @return array Menu items or empty array
	 */
	public static function get_menu_items_by_location( $location ) {
		$menu = self::get_menu_by_location( $location );

		if ( ! $menu ) {
			return [];
		}

		return wp_get_nav_menu_items( $menu->term_id );
	}

	/**
	 * Get all assigned menu locations
	 *
	 * @return array Locations with assigned menus
	 * [
	 *   'primary-nav' => WP_Term object,
	 *   'footer-column-1' => WP_Term object,
	 *   ...
	 * ]
	 */
	public static function get_assigned_menus() {
		$locations = get_registered_nav_menus();
		$assigned  = [];

		foreach ( array_keys( $locations ) as $location ) {
			$menu = self::get_menu_by_location( $location );
			if ( $menu ) {
				$assigned[ $location ] = $menu;
			}
		}

		return $assigned;
	}

	/**
	 * Check if a menu is assigned to a location
	 */
	public static function is_menu_assigned( $location ) {
		return self::get_menu_by_location( $location ) !== null;
	}

	/**
	 * Get all menu locations
	 *
	 * Useful for iterating and checking which menus are assigned
	 */
	public static function get_all_locations() {
		return get_registered_nav_menus();
	}

	/**
	 * Register REST API routes for menu management
	 */
	public static function register_rest_routes() {
		// Get all WordPress menus
		register_rest_route(
			'flatwp/v1',
			'/menus',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_all_menus' ],
				'permission_callback' => function() {
					return current_user_can( 'edit_theme_options' );
				},
			]
		);

		// Get menu locations and assignments
		register_rest_route(
			'flatwp/v1',
			'/menu-locations',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_menu_locations' ],
				'permission_callback' => function() {
					return current_user_can( 'edit_theme_options' );
				},
			]
		);

		// Update menu location assignments
		register_rest_route(
			'flatwp/v1',
			'/menu-locations',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'update_menu_locations' ],
				'permission_callback' => function() {
					return current_user_can( 'edit_theme_options' );
				},
			]
		);
	}

	/**
	 * REST endpoint: Get all WordPress menus
	 */
	public static function get_all_menus( $request ) {
		$menus = wp_get_nav_menus();

		$formatted_menus = array_map( function( $menu ) {
			return [
				'id'   => $menu->term_id,
				'name' => $menu->name,
				'slug' => $menu->slug,
			];
		}, $menus );

		return rest_ensure_response( $formatted_menus );
	}

	/**
	 * REST endpoint: Get menu locations with current assignments
	 */
	public static function get_menu_locations( $request ) {
		$locations   = get_registered_nav_menus();
		$assignments = get_nav_menu_locations();

		$result = [];

		foreach ( $locations as $location => $description ) {
			$menu_id = isset( $assignments[ $location ] ) ? $assignments[ $location ] : 0;
			$menu    = $menu_id ? wp_get_nav_menu_object( $menu_id ) : null;

			$result[] = [
				'location'    => $location,
				'description' => $description,
				'menu_id'     => $menu_id,
				'menu_name'   => $menu ? $menu->name : '',
			];
		}

		return rest_ensure_response( $result );
	}

	/**
	 * REST endpoint: Update menu location assignments
	 */
	public static function update_menu_locations( $request ) {
		$assignments = $request->get_json_params();

		if ( ! is_array( $assignments ) ) {
			return new WP_Error( 'invalid_data', 'Invalid menu assignments data' );
		}

		// Get current assignments
		$current = get_nav_menu_locations();

		// Update with new assignments
		foreach ( $assignments as $location => $menu_id ) {
			if ( array_key_exists( $location, get_registered_nav_menus() ) ) {
				$current[ $location ] = intval( $menu_id );
			}
		}

		// Save the assignments
		set_theme_mod( 'nav_menu_locations', $current );

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Menu locations updated successfully',
		] );
	}
}
