<?php
/**
 * FlatWP Webhook Plugin - Yoast SEO Integration
 *
 * Tracks SEO metadata changes and includes SEO data in revalidation payloads.
 *
 * @package FlatWP
 * @since 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Webhook_Plugin_Yoast_SEO {

	/**
	 * Initialize plugin
	 */
	public static function init() {
		// Check if Yoast SEO is active
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return;
		}

		// Register webhook plugin
		FlatWP_Webhook_Plugin_Manager::register_plugin(
			'yoast-seo',
			array(
				'name'            => 'Yoast SEO',
				'description'     => 'Track SEO metadata changes and include SEO data in webhooks',
				'version'         => '1.0',
				'triggers'        => array(
					'yoast_seo_meta_updated',
					'yoast_seo_readability_updated',
				),
				'payload_builder' => array( __CLASS__, 'build_payload' ),
				'settings'        => array(
					array(
						'key'   => 'include_seo_score',
						'label' => 'Include SEO Score',
						'type'  => 'checkbox',
					),
					array(
						'key'   => 'include_focus_keyword',
						'label' => 'Include Focus Keyword',
						'type'  => 'checkbox',
					),
				),
			)
		);

		// Hook into Yoast meta updates
		add_action( 'wpseo_meta_updated', array( __CLASS__, 'on_meta_updated' ), 10, 2 );
	}

	/**
	 * Handle Yoast meta updates
	 */
	public static function on_meta_updated( $post_id, $meta ) {
		// Trigger webhook with Yoast data
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$payload = array(
			'post_id'   => $post_id,
			'post_type' => $post->post_type,
			'yoast_seo' => self::get_seo_data( $post_id ),
		);

		FlatWP_Webhook_Dispatcher::dispatch( 'yoast_seo_meta_updated', $payload );
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
			'seo_data' => self::get_seo_data( $post_id ),
		);
	}

	/**
	 * Get Yoast SEO data for a post
	 */
	private static function get_seo_data( $post_id ) {
		$seo_data = array();

		// Get Yoast meta
		$meta = WPSEO_Meta::get_meta( 'wpseo_meta', $post_id );

		if ( ! empty( $meta ) ) {
			$seo_data = array(
				'focus_keyword'        => $meta['focus-keyword'] ?? '',
				'seo_score'            => $meta['seo-score'] ?? null,
				'readability_score'    => $meta['readability-score'] ?? null,
				'metadesc'             => $meta['metadesc'] ?? '',
				'title'                => $meta['title'] ?? '',
				'canonical'            => $meta['canonical'] ?? '',
				'robots'               => $meta['robots'] ?? '',
				'keyword_synonyms'     => $meta['keyword-synonyms'] ?? '',
			);
		}

		return apply_filters( 'flatwp_webhook_yoast_seo_data', $seo_data, $post_id );
	}
}

// Auto-initialize when revalidation is active
if ( did_action( 'flatwp_init' ) ) {
	FlatWP_Webhook_Plugin_Yoast_SEO::init();
} else {
	add_action( 'flatwp_init', array( 'FlatWP_Webhook_Plugin_Yoast_SEO', 'init' ) );
}
