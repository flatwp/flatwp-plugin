<?php
/**
 * FlatWP GraphQL Menu Locations
 *
 * Registers custom menu locations with WPGraphQL and exposes custom fields
 *
 * @package FlatWP
 */

class FlatWP_GraphQL_Menu_Locations {
	/**
	 * Initialize GraphQL extensions for menu locations
	 */
	public static function init() {
		add_action( 'graphql_register_types', [ self::class, 'register_menu_locations' ] );
		add_filter( 'graphql_menu_item_fields', [ self::class, 'add_custom_fields_to_menu_items' ] );
		add_filter( 'graphql_resolve_menu_item', [ self::class, 'resolve_custom_menu_fields' ], 10, 2 );
	}

	/**
	 * Register custom menu locations with WPGraphQL
	 */
	public static function register_menu_locations() {
		// WPGraphQL automatically registers menu locations from WordPress
		// We don't need to manually register them as enum values
		// The MenuLocationEnum is already populated by WPGraphQL core

		// If you need to add custom locations, use register_nav_menus() in WordPress
		// and WPGraphQL will automatically expose them
	}

	/**
	 * Add custom fields to menu items in GraphQL schema
	 */
	public static function add_custom_fields_to_menu_items( $fields ) {
		$fields['flatwpIcon'] = [
			'type'        => 'String',
			'description' => 'Custom icon for the menu item (Lucide icon name)',
		];

		$fields['flatwpBadge'] = [
			'type'        => 'String',
			'description' => 'Custom badge text for the menu item',
		];

		return $fields;
	}

	/**
	 * Resolve custom menu item fields
	 */
	public static function resolve_custom_menu_fields( $menu_item, $source ) {
		// Get the menu item ID
		$menu_item_id = $source->ID ?? $source->databaseId ?? null;

		if ( ! $menu_item_id ) {
			return $menu_item;
		}

		// Add custom field values
		$menu_item['flatwpIcon'] = get_post_meta( $menu_item_id, 'flatwp_icon', true );
		$menu_item['flatwpBadge'] = get_post_meta( $menu_item_id, 'flatwp_badge', true );

		return $menu_item;
	}
}