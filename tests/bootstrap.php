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
function get_object_taxonomies() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function get_terms() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }
function is_admin() { return (bool) wpmc_test_call( __FUNCTION__ ); }
function is_main_query() { return (bool) wpmc_test_call( __FUNCTION__ ); }
function register_taxonomy() { return wpmc_test_call( __FUNCTION__, func_get_args() ); }

class Walker {}
class Walker_CategoryDropdown extends Walker {}
class WP_Query {}
class WP_Widget {}

require_once dirname( __DIR__ ) . '/wp-media-categories/includes/walkers.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/admin.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/functions.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/taxonomies.php';
require_once dirname( __DIR__ ) . '/wp-media-categories/includes/ajax.php';
