<?php
/**
 * Page Settings Class
 *
 * Handles per-page settings via meta box in the WordPress admin.
 *
 * @package FlatWP
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP Page Settings Class
 */
class FlatWP_Page_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Add meta box to page editor.
	 */
	public function add_meta_box() {
		add_meta_box(
			'flatwp_page_settings',
			__( 'FlatWP Page Settings', 'flatwp-wordpress-connector' ),
			array( $this, 'render_meta_box' ),
			'page',
			'side',
			'default'
		);
	}

	/**
	 * Render meta box content.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'flatwp_page_settings_nonce', 'flatwp_page_settings_nonce' );

		// Get current values.
		$hide_title       = (bool) get_post_meta( $post->ID, '_flatwp_hide_title', true );
		$container_width  = get_post_meta( $post->ID, '_flatwp_container_width', true ) ?: 'default';
		$hide_header      = (bool) get_post_meta( $post->ID, '_flatwp_hide_header', true );
		$hide_footer      = (bool) get_post_meta( $post->ID, '_flatwp_hide_footer', true );
		$custom_css_class = get_post_meta( $post->ID, '_flatwp_custom_css_class', true );

		?>
		<div class="flatwp-page-settings">
			<style>
				.flatwp-page-settings {
					font-size: 13px;
				}
				.flatwp-page-settings p {
					margin: 12px 0;
				}
				.flatwp-page-settings label {
					display: flex;
					align-items: center;
					gap: 8px;
					cursor: pointer;
				}
				.flatwp-page-settings input[type="checkbox"] {
					margin: 0;
				}
				.flatwp-page-settings select,
				.flatwp-page-settings input[type="text"] {
					width: 100%;
					margin-top: 4px;
				}
				.flatwp-page-settings .setting-label {
					font-weight: 600;
					display: block;
					margin-bottom: 4px;
				}
				.flatwp-page-settings .setting-description {
					font-size: 12px;
					color: #666;
					margin-top: 4px;
				}
			</style>

			<!-- Hide Title -->
			<p>
				<label>
					<input
						type="checkbox"
						name="flatwp_hide_title"
						value="1"
						<?php checked( $hide_title, true ); ?>
					/>
					<span><?php esc_html_e( 'Hide Page Title', 'flatwp-wordpress-connector' ); ?></span>
				</label>
			</p>

			<!-- Container Width -->
			<p>
				<span class="setting-label">
					<?php esc_html_e( 'Container Width', 'flatwp-wordpress-connector' ); ?>
				</span>
				<select name="flatwp_container_width">
					<option value="default" <?php selected( $container_width, 'default' ); ?>>
						<?php esc_html_e( 'Default', 'flatwp-wordpress-connector' ); ?>
					</option>
					<option value="contained" <?php selected( $container_width, 'contained' ); ?>>
						<?php esc_html_e( 'Contained', 'flatwp-wordpress-connector' ); ?>
					</option>
					<option value="full-width" <?php selected( $container_width, 'full-width' ); ?>>
						<?php esc_html_e( 'Full Width', 'flatwp-wordpress-connector' ); ?>
					</option>
				</select>
			</p>

			<!-- Hide Header -->
			<p>
				<label>
					<input
						type="checkbox"
						name="flatwp_hide_header"
						value="1"
						<?php checked( $hide_header, true ); ?>
					/>
					<span><?php esc_html_e( 'Hide Site Header', 'flatwp-wordpress-connector' ); ?></span>
				</label>
			</p>

			<!-- Hide Footer -->
			<p>
				<label>
					<input
						type="checkbox"
						name="flatwp_hide_footer"
						value="1"
						<?php checked( $hide_footer, true ); ?>
					/>
					<span><?php esc_html_e( 'Hide Site Footer', 'flatwp-wordpress-connector' ); ?></span>
				</label>
			</p>

			<!-- Custom CSS Class -->
			<p>
				<span class="setting-label">
					<?php esc_html_e( 'Custom CSS Classes', 'flatwp-wordpress-connector' ); ?>
				</span>
				<input
					type="text"
					name="flatwp_custom_css_class"
					value="<?php echo esc_attr( $custom_css_class ); ?>"
					placeholder="<?php esc_attr_e( 'my-custom-class another-class', 'flatwp-wordpress-connector' ); ?>"
				/>
				<span class="setting-description">
					<?php esc_html_e( 'Space-separated CSS classes for custom styling.', 'flatwp-wordpress-connector' ); ?>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Check if this is a page.
		if ( 'page' !== $post->post_type ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['flatwp_page_settings_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['flatwp_page_settings_nonce'], 'flatwp_page_settings_nonce' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

		// Save hide title.
		$hide_title = isset( $_POST['flatwp_hide_title'] ) ? 1 : 0;
		update_post_meta( $post_id, '_flatwp_hide_title', $hide_title );

		// Save container width.
		$container_width = isset( $_POST['flatwp_container_width'] )
			? sanitize_text_field( $_POST['flatwp_container_width'] )
			: 'default';
		$allowed_widths = array( 'default', 'contained', 'full-width' );
		if ( ! in_array( $container_width, $allowed_widths, true ) ) {
			$container_width = 'default';
		}
		update_post_meta( $post_id, '_flatwp_container_width', $container_width );

		// Save hide header.
		$hide_header = isset( $_POST['flatwp_hide_header'] ) ? 1 : 0;
		update_post_meta( $post_id, '_flatwp_hide_header', $hide_header );

		// Save hide footer.
		$hide_footer = isset( $_POST['flatwp_hide_footer'] ) ? 1 : 0;
		update_post_meta( $post_id, '_flatwp_hide_footer', $hide_footer );

		// Save custom CSS class.
		$custom_css_class = isset( $_POST['flatwp_custom_css_class'] )
			? sanitize_text_field( $_POST['flatwp_custom_css_class'] )
			: '';
		update_post_meta( $post_id, '_flatwp_custom_css_class', $custom_css_class );
	}

	/**
	 * Get settings for a page.
	 *
	 * @param int $post_id Post ID.
	 * @return array Page settings.
	 */
	public function get_settings( $post_id ) {
		return array(
			'hideTitle'      => (bool) get_post_meta( $post_id, '_flatwp_hide_title', true ),
			'containerWidth' => get_post_meta( $post_id, '_flatwp_container_width', true ) ?: 'default',
			'hideHeader'     => (bool) get_post_meta( $post_id, '_flatwp_hide_header', true ),
			'hideFooter'     => (bool) get_post_meta( $post_id, '_flatwp_hide_footer', true ),
			'customCssClass' => get_post_meta( $post_id, '_flatwp_custom_css_class', true ) ?: '',
		);
	}
}
