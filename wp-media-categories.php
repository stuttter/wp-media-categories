<?php

/**
 * Plugin Name: WP Media Categories
 * Plugin URI:  https://wordpress.org/plugins/wp-media-categories/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-media-categories
 * Domain Path: /lang
 * Description: Categories for media & attachments
 * Version:     0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Include the Media Categories files
 *
 * @since 0.1.0
 */
function _wp_media_categories() {

	// Get the plugin path
	$plugin_path = plugin_dir_path( __FILE__ );

	// Admin-only common files
	require $plugin_path . 'includes/admin.php';
	require $plugin_path . 'includes/ajax.php';
	require $plugin_path . 'includes/functions.php';
	require $plugin_path . 'includes/taxonomies.php';
	require $plugin_path . 'includes/walkers.php';
	require $plugin_path . 'includes/widgets.php';
	require $plugin_path . 'includes/hooks.php';
}
add_action( 'plugins_loaded', '_wp_media_categories' );

/**
 * Return the plugin's URL
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_media_categories_get_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Return the asset version
 *
 * @since 0.1.0
 *
 * @return int
 */
function wp_media_categories_get_asset_version() {
	return 201510220001;
}
