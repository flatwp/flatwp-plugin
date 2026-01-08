<?php
/**
 * FlatWP Settings Class
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlatWP_Settings {
	public function __construct() {
		// Settings initialization
	}

	public function get( $key, $default = null ) {
		return flatwp()->get_setting( $key, $default );
	}

	public function set( $key, $value ) {
		return flatwp()->update_setting( $key, $value );
	}
}
