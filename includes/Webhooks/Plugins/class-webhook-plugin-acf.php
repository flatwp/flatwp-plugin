<?php
/**
 * FlatWP Webhook Plugin - Advanced Custom Fields Integration
 *
 * Includes ACF field data in webhook payloads.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Plugin_ACF {

	/**
	 * Initialize plugin
	 */
	public static function init() {
		// Check if ACF is active
		if ( ! function_exists( 'get_field' ) ) {
			return;
		}

		// Register webhook plugin
		FlatWP_Webhook_Plugin_Manager::register_plugin(
			'acf',
			array(
				'name'            => 'Advanced Custom Fields',
				'description'     => 'Include ACF field data in webhook payloads',
				'version'         => '1.0',
				'triggers'        => array(
					'acf_save_post',
					'acf_updated_field',
				),
				'payload_builder' => array( __CLASS__, 'build_payload' ),
				'settings'        => array(
					array(
						'key'   => 'include_all_fields',
						'label' => 'Include All ACF Fields',
						'type'  => 'checkbox',
					),
					array(
						'key'   => 'field_groups',
						'label' => 'Select Field Groups',
						'type'  => 'multiselect',
						'options' => array(),
					),
				),
			)
		);

		// Hook into ACF save
		add_action( 'acf/save_post', array( __CLASS__, 'on_acf_save' ), 20, 1 );
	}

	/**
	 * Handle ACF save
	 */
	public static function on_acf_save( $post_id ) {
		// Skip if not a published post
		$post = get_post( $post_id );
		if ( ! $post || $post->post_status !== 'publish' ) {
			return;
		}

		$payload = array(
			'post_id'   => $post_id,
			'post_type' => $post->post_type,
			'acf_data'  => self::get_acf_data( $post_id ),
		);

		FlatWP_Webhook_Dispatcher::dispatch( 'acf_save_post', $payload );
	}

	/**
	 * Build webhook payload
	 */
	public static function build_payload( $event_data = array() ) {
		$post_id = $event_data['post_id'] ?? null;

		if ( ! $post_id ) {
			return array();
		}

		return array(
			'acf_fields' => self::get_acf_data( $post_id ),
		);
	}

	/**
	 * Get ACF data for a post
	 */
	private static function get_acf_data( $post_id ) {
		$acf_data = array();

		if ( function_exists( 'get_fields' ) ) {
			$fields = get_fields( $post_id );

			if ( $fields ) {
				// Filter sensitive data (passwords, etc.)
				foreach ( $fields as $field_name => $field_value ) {
					if ( ! self::is_sensitive_field( $field_name ) ) {
						$acf_data[ $field_name ] = $field_value;
					}
				}
			}
		}

		return apply_filters( 'flatwp_webhook_acf_data', $acf_data, $post_id );
	}

	/**
	 * Check if field is sensitive
	 */
	private static function is_sensitive_field( $field_name ) {
		$sensitive_keywords = array(
			'password',
			'secret',
			'api_key',
			'token',
			'private',
		);

		foreach ( $sensitive_keywords as $keyword ) {
			if ( stripos( $field_name, $keyword ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'flatwp_webhook_acf_is_sensitive', false, $field_name );
	}
}

// Auto-initialize when revalidation is active
if ( did_action( 'flatwp_init' ) ) {
	FlatWP_Webhook_Plugin_ACF::init();
} else {
	add_action( 'flatwp_init', array( 'FlatWP_Webhook_Plugin_ACF', 'init' ) );
}
