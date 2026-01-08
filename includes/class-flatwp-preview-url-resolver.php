<?php
/**
 * FlatWP Preview URL Resolver Class
 *
 * Resolves preview URL templates by replacing placeholders with post data.
 * Supports flexible URL patterns like: /api/preview?secret={secret}&id={ID}&slug={slug}&type={type}
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Preview_URL_Resolver {
	/**
	 * Placeholder regex pattern for {name} style placeholders
	 *
	 * @var string
	 */
	private const PLACEHOLDER_REGEX = '/\{([A-Za-z0-9_]+)\}/';

	/**
	 * Parameter registry instance
	 *
	 * @var FlatWP_Parameter_Registry
	 */
	private FlatWP_Parameter_Registry $registry;

	/**
	 * Constructor
	 *
	 * @param FlatWP_Parameter_Registry $registry Parameter registry
	 */
	public function __construct( FlatWP_Parameter_Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Resolve all placeholders in a URL template
	 *
	 * @param string  $template URL template with placeholders
	 * @param WP_Post $post     Post object for parameter resolution
	 * @return string Resolved URL
	 */
	public function resolve( string $template, WP_Post $post ): string {
		return (string) preg_replace_callback(
			self::PLACEHOLDER_REGEX,
			fn( array $matches ) => $this->resolve_placeholder( $matches[1], $post ),
			$template
		);
	}

	/**
	 * Resolve a single placeholder
	 *
	 * @param string  $name Parameter name
	 * @param WP_Post $post Post object
	 * @return string Resolved parameter value or placeholder if not found
	 */
	private function resolve_placeholder( string $name, WP_Post $post ): string {
		$value = $this->registry->resolve( $name, $post );

		if ( null === $value ) {
			// Keep placeholder if parameter not found
			return '{' . $name . '}';
		}

		// URL encode the value for safe URL inclusion
		return rawurlencode( $value );
	}
}
