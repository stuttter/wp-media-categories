<?php

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$GLOBALS['wpmc_test'] = array();

function wpmc_test_call( $name, $arguments = array() ) {
	$GLOBALS['wpmc_test']['calls'][ $name ][] = $arguments;

	if ( isset( $GLOBALS['wpmc_test']['callbacks'][ $name ] ) ) {
		return call_user_func_array( $GLOBALS['wpmc_test']['callbacks'][ $name ], $arguments );
	}

	return isset( $GLOBALS['wpmc_test']['returns'][ $name ] )
		? $GLOBALS['wpmc_test']['returns'][ $name ]
		: null;
}

function __( $text ) { return $text; }
function absint( $value ) { return abs( (int) $value ); }
function add_action() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function add_filter() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function add_shortcode() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function apply_filters( $hook, $value ) { return wpmc_test_call( __FUNCTION__ . ':' . $hook, func_get_args() ) ?: $value; }
function check_admin_referer() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function current_user_can() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function get_object_taxonomies() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function get_terms() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function has_term() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function is_admin() { return (bool) wpmc_test_call( __FUNCTION__ ); }
function is_main_query() { return (bool) wpmc_test_call( __FUNCTION__ ); }
function is_wp_error() { return (bool) wpmc_test_call( __FUNCTION__, func_get_args() ); }
function register_taxonomy() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function sanitize_key( $key ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', $key ) ); }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_unslash( $value ) {
	return is_array( $value )
		? array_map( 'wp_unslash', $value )
		: stripslashes( $value );
}
function wp_json_encode( $value, $options = 0 ) { return json_encode( $value, $options ); }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function wp_remove_object_terms() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function wp_set_object_terms() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }

class Walker {}
class Walker_CategoryDropdown extends Walker {}
class WP_Query {}
class WP_Widget {
	public $id_base;
	public $number;

	public function __construct( $id_base = '' ) {
		$this->id_base = $id_base;
		$this->number  = 1;
	}
}

require_once dirname( __DIR__ ) . '/wp-media-categories/includes/walkers.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/admin.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/functions.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/taxonomies.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/ajax.php';
