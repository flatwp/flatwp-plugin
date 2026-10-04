<?php
/**
 * FlatWP Menu Editor Enhancement
 *
 * Adds custom fields (icon and badge) to WordPress menu items
 * in the admin menu editor interface.
 *
 * @package FlatWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Menu_Editor {
	/**
	 * Initialize menu editor enhancements
	 */
	public static function init() {
		// Add custom fields to menu item edit screen
		add_action( 'wp_nav_menu_item_custom_fields', [ self::class, 'add_custom_fields' ], 10, 2 );

		// Save custom fields when menu is updated
		add_action( 'wp_update_nav_menu_item', [ self::class, 'save_custom_fields' ], 10, 2 );

		// Add admin styles for better UI
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_admin_styles' ] );

		// Add JavaScript for icon preview
		add_action( 'admin_footer', [ self::class, 'add_admin_scripts' ] );
	}

	/**
	 * Add custom fields to menu item edit form
	 *
	 * @param int    $item_id Menu item ID
	 * @param object $item    Menu item object
	 */
	public static function add_custom_fields( $item_id, $item ) {
		$icon_value  = get_post_meta( $item_id, 'flatwp_icon', true );
		$badge_value = get_post_meta( $item_id, 'flatwp_badge', true );
		?>
		<div class="field-flatwp-icon description-wide">
			<p class="description">
				<label for="edit-menu-item-flatwp-icon-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Icon (Lucide)', 'flatwp-headless-connector' ); ?><br />
					<input
						type="text"
						id="edit-menu-item-flatwp-icon-<?php echo esc_attr( $item_id ); ?>"
						class="widefat edit-menu-item-flatwp-icon"
						name="menu-item-flatwp-icon[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $icon_value ); ?>"
						placeholder="e.g., book-open, shield, rocket"
					/>
					<span class="description" style="margin-top: 5px; display: block; font-size: 11px;">
						Enter a Lucide icon name. Common icons: home, users, settings, file-text, book-open,
						shield, rocket, zap, building-2, briefcase, dollar-sign, help-circle, newspaper, code-2
					</span>
				</label>
			</p>
		</div>
		<div class="field-flatwp-badge description-wide">
			<p class="description">
				<label for="edit-menu-item-flatwp-badge-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Badge Text', 'flatwp-headless-connector' ); ?><br />
					<input
						type="text"
						id="edit-menu-item-flatwp-badge-<?php echo esc_attr( $item_id ); ?>"
						class="widefat edit-menu-item-flatwp-badge"
						name="menu-item-flatwp-badge[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $badge_value ); ?>"
						placeholder="e.g., NEW, BETA, PRO"
					/>
					<span class="description" style="margin-top: 5px; display: block; font-size: 11px;">
						Optional badge to display next to the menu item (e.g., NEW, BETA, COMING SOON)
					</span>
				</label>
			</p>
		</div>
		<div class="field-flatwp-header description-wide" style="margin-top: 10px;">
			<p class="description" style="background: #f0f8ff; padding: 10px; border-radius: 3px; border-left: 3px solid #2271b1;">
				<strong>💡 Pro Tip:</strong> To create a non-clickable section header, enter <code>##</code> in the URL field above.
				This will display the menu item as a label without a link.
			</p>
		</div>
		<?php
	}

	/**
	 * Save custom field values when menu is saved
	 *
	 * @param int $menu_id         ID of the updated menu
	 * @param int $menu_item_db_id ID of the updated menu item
	 */
	public static function save_custom_fields( $menu_id, $menu_item_db_id ) {
		// Check if icon field was submitted
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core menu save
		if ( isset( $_POST['menu-item-flatwp-icon'][ $menu_item_db_id ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core menu save
			$icon_value = sanitize_text_field( wp_unslash( $_POST['menu-item-flatwp-icon'][ $menu_item_db_id ] ) );
			update_post_meta( $menu_item_db_id, 'flatwp_icon', $icon_value );
		}

		// Check if badge field was submitted
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core menu save
		if ( isset( $_POST['menu-item-flatwp-badge'][ $menu_item_db_id ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core menu save
			$badge_value = sanitize_text_field( wp_unslash( $_POST['menu-item-flatwp-badge'][ $menu_item_db_id ] ) );
			update_post_meta( $menu_item_db_id, 'flatwp_badge', $badge_value );
		}
	}

	/**
	 * Enqueue admin styles for menu editor
	 */
	public static function enqueue_admin_styles( $hook ) {
		if ( 'nav-menus.php' !== $hook ) {
			return;
		}

		// Add inline styles for better field presentation
		wp_add_inline_style( 'wp-admin', '
			.field-flatwp-icon,
			.field-flatwp-badge,
			.field-flatwp-header {
				margin-top: 12px;
			}
			.field-flatwp-icon label,
			.field-flatwp-badge label {
				font-weight: 600;
				color: #1e1e1e;
			}
			.field-flatwp-icon input,
			.field-flatwp-badge input {
				margin-top: 5px;
			}
			.flatwp-icon-preview {
				display: inline-block;
				width: 20px;
				height: 20px;
				margin-left: 10px;
				vertical-align: middle;
			}
		' );
	}

	/**
	 * Add JavaScript for enhanced UX
	 */
	public static function add_admin_scripts() {
		$screen = get_current_screen();
		if ( 'nav-menus' !== $screen->base ) {
			return;
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Add icon name autocomplete suggestions
			var iconSuggestions = [
				'home', 'users', 'settings', 'file-text', 'book-open',
				'shield', 'rocket', 'zap', 'building-2', 'briefcase',
				'dollar-sign', 'help-circle', 'newspaper', 'code-2',
				'sparkles', 'bar-chart-3', 'external-link', 'github',
				'mail', 'phone', 'map-pin', 'link-2', 'globe',
				'lock', 'unlock', 'eye', 'eye-off', 'search',
				'menu', 'x', 'plus', 'minus', 'check',
				'arrow-right', 'arrow-left', 'arrow-up', 'arrow-down',
				'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down',
				'download', 'upload', 'refresh-cw', 'play', 'pause'
			];

			// Helper function to show icon hints
			function addIconHints() {
				$('.edit-menu-item-flatwp-icon').each(function() {
					if (!$(this).data('hints-added')) {
						$(this).attr('list', 'flatwp-icon-suggestions');
						$(this).data('hints-added', true);
					}
				});
			}

			// Create datalist for icon suggestions if it doesn't exist
			if (!$('#flatwp-icon-suggestions').length) {
				var datalist = $('<datalist id="flatwp-icon-suggestions"></datalist>');
				iconSuggestions.forEach(function(icon) {
					datalist.append('<option value="' + icon + '">');
				});
				$('body').append(datalist);
			}

			// Apply hints to existing fields
			addIconHints();

			// Apply hints when new menu items are added
			$(document).on('menu-item-added', function() {
				setTimeout(addIconHints, 100);
			});

			// Show helper when ## is entered in URL field
			$(document).on('input', '.edit-menu-item-url', function() {
				if ($(this).val() === '##') {
					var $container = $(this).closest('.menu-item-settings');
					var $helper = $container.find('.flatwp-header-helper');

					if (!$helper.length) {
						$container.append(
							'<div class="flatwp-header-helper" style="background: #fff3cd; padding: 8px; margin-top: 10px; border-radius: 3px; border: 1px solid #ffc107;">' +
							'<strong>ℹ️ Section Header Mode:</strong> This item will display as a non-clickable header.' +
							'</div>'
						);
					}
				} else {
					$(this).closest('.menu-item-settings').find('.flatwp-header-helper').remove();
				}
			});

			// Initialize on page load for existing ## URLs
			$('.edit-menu-item-url').each(function() {
				if ($(this).val() === '##') {
					$(this).trigger('input');
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Get list of available Lucide icons
	 * This matches the iconMap in the frontend NavigationBlock component
	 */
	public static function get_available_icons() {
		return [
			'book-open'    => 'Book Open',
			'code-2'       => 'Code',
			'github'       => 'GitHub',
			'file-text'    => 'File Text',
			'sparkles'     => 'Sparkles',
			'newspaper'    => 'Newspaper',
			'help-circle'  => 'Help Circle',
			'zap'          => 'Lightning',
			'shield'       => 'Shield',
			'bar-chart-3'  => 'Bar Chart',
			'rocket'       => 'Rocket',
			'building-2'   => 'Building',
			'briefcase'    => 'Briefcase',
			'dollar-sign'  => 'Dollar Sign',
			'users'        => 'Users',
			'home'         => 'Home',
			'settings'     => 'Settings',
			'mail'         => 'Mail',
			'phone'        => 'Phone',
			'map-pin'      => 'Map Pin',
			'link-2'       => 'Link',
			'globe'        => 'Globe',
			'lock'         => 'Lock',
			'search'       => 'Search',
			'menu'         => 'Menu',
			'external-link'=> 'External Link',
		];
	}
}