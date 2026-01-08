<?php
/**
 * FlatWP Menus GraphQL Integration
 *
 * Exposes menu locations and items to GraphQL API
 *
 * @package FlatWP
 */

class FlatWP_Menus_GraphQL {
	/**
	 * Initialize GraphQL integration
	 */
	public static function init() {
		add_action( 'graphql_register_types', [ self::class, 'register_types' ] );
		add_action( 'graphql_register_types', [ self::class, 'register_fields' ] );
	}

	/**
	 * Register custom GraphQL types
	 */
	public static function register_types() {
		// Menu by location type
		register_graphql_object_type(
			'MenuByLocation',
			[
				'description' => 'Menu assigned to a location',
				'fields'      => [
					'location'   => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => 'Menu location slug (primary-nav, footer-column-1, etc)',
					],
					'menu'       => [
						'type'        => 'Menu',
						'description' => 'The WordPress menu object',
						'resolve'     => function( $menu_location ) {
							return FlatWP_Menu_Locations::get_menu_by_location( $menu_location['location'] );
						},
					],
					'menuItems'  => [
						'type'        => [ 'list_of' => 'MenuItem' ],
						'description' => 'Menu items',
						'resolve'     => function( $menu_location ) {
							$menu = FlatWP_Menu_Locations::get_menu_by_location( $menu_location['location'] );
							if ( ! $menu ) {
								return [];
							}

							$items = wp_get_nav_menu_items( $menu->term_id );
							return $items ? array_values( $items ) : [];
						},
					],
				],
			]
		);

		// Site settings type
		register_graphql_object_type(
			'SiteSettings',
			[
				'description' => 'FlatWP site settings',
				'fields'      => [
					'siteDescription' => [
						'type'        => 'String',
						'description' => 'Site description',
					],
					'socialLinks'     => [
						'type'        => [ 'list_of' => 'SocialLink' ],
						'description' => 'Social media links',
					],
					'contactInfo'     => [
						'type'        => 'ContactInfo',
						'description' => 'Contact information',
					],
				],
			]
		);

		// Social link type
		register_graphql_object_type(
			'SocialLink',
			[
				'description' => 'Social media link',
				'fields'      => [
					'id'       => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => 'Link ID',
					],
					'label'    => [
						'type'        => 'String',
						'description' => 'Link label/text',
					],
					'platform' => [
						'type'        => 'String',
						'description' => 'Social platform name',
					],
					'url'      => [
						'type'        => 'String',
						'description' => 'Link URL',
					],
					'icon'     => [
						'type'        => 'String',
						'description' => 'Icon name',
					],
				],
			]
		);

		// Contact info type
		register_graphql_object_type(
			'ContactInfo',
			[
				'description' => 'Contact information',
				'fields'      => [
					'email'   => [
						'type'        => 'String',
						'description' => 'Email address',
					],
					'phone'   => [
						'type'        => 'String',
						'description' => 'Phone number',
					],
					'address' => [
						'type'        => 'String',
						'description' => 'Physical address',
					],
				],
			]
		);
	}

	/**
	 * Register menus field on RootQuery
	 */
	public static function register_fields() {
		// Register custom fields on WPGraphQL's MenuItem type
		register_graphql_field(
			'MenuItem',
			'flatwpIcon',
			[
				'type'        => 'String',
				'description' => 'Lucide icon name for menu item',
				'resolve'     => function( $item ) {
					try {
						// WPGraphQL passes the database ID directly
						$id = isset( $item->databaseId ) ? $item->databaseId : ( isset( $item->ID ) ? $item->ID : null );
						if ( ! $id ) {
							error_log( 'FlatWP flatwpIcon: No ID found on item. Available properties: ' . print_r( $item, true ) );
							return '';
						}
						return FlatWP_Site_Settings::get_menu_item_icon( $id );
					} catch ( Exception $e ) {
						error_log( 'FlatWP flatwpIcon error: ' . $e->getMessage() );
						return '';
					}
				},
			]
		);

		register_graphql_field(
			'MenuItem',
			'flatwpBadge',
			[
				'type'        => 'String',
				'description' => 'Badge text for menu item',
				'resolve'     => function( $item ) {
					try {
						// WPGraphQL passes the database ID directly
						$id = isset( $item->databaseId ) ? $item->databaseId : ( isset( $item->ID ) ? $item->ID : null );
						if ( ! $id ) {
							error_log( 'FlatWP flatwpBadge: No ID found on item' );
							return '';
						}
						return FlatWP_Site_Settings::get_menu_item_badge( $id );
					} catch ( Exception $e ) {
						error_log( 'FlatWP flatwpBadge error: ' . $e->getMessage() );
						return '';
					}
				},
			]
		);

		// Get menus by location
		register_graphql_field(
			'RootQuery',
			'menusByLocation',
			[
				'type'        => [ 'list_of' => 'MenuByLocation' ],
				'description' => 'Menus assigned to locations',
				'args'        => [
					'location' => [
						'type'        => 'String',
						'description' => 'Specific location (primary-nav, footer-column-1, etc). Omit to get all.',
					],
				],
				'resolve'     => function( $root, $args ) {
					if ( isset( $args['location'] ) && ! empty( $args['location'] ) ) {
						// Return specific location
						$menu = FlatWP_Menu_Locations::get_menu_by_location( $args['location'] );
						if ( ! $menu ) {
							return [];
						}

						return [
							[
								'location' => $args['location'],
								'menu'     => $menu,
							],
						];
					}

					// Return all locations with assigned menus
					$locations = FlatWP_Menu_Locations::get_assigned_menus();
					$result    = [];

					foreach ( $locations as $location => $menu ) {
						$result[] = [
							'location' => $location,
							'menu'     => $menu,
						];
					}

					return $result;
				},
			]
		);

		// Get site settings
		register_graphql_field(
			'RootQuery',
			'siteSettings',
			[
				'type'        => 'SiteSettings',
				'description' => 'FlatWP site settings',
				'resolve'     => function() {
					return FlatWP_Site_Settings::get_option();
				},
			]
		);
	}
}
