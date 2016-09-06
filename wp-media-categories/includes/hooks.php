<?php

/**
 * Media Categories Actions & Filters
 *
 * @package Media/Categories/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Init
add_action( 'init', 'wp_media_categories_register_media_taxonomy' );
add_action( 'init', 'wp_media_categories_register_widgets'        );

// Admin
add_action( 'admin_enqueue_scripts',	'wp_media_categories_enqueue_admin_scripts' );
add_action( 'admin_footer-upload.php',	'wp_media_categories_custom_bulk_admin_footer' );
add_action( 'admin_notices',			'wp_media_categories_custom_bulk_admin_notices' );
add_action( 'load-upload.php',			'wp_media_categories_custom_bulk_action' );

// Save attachments
add_action( 'add_attachment',  'wp_media_categories_set_attachment_category' );
add_action( 'edit_attachment', 'wp_media_categories_set_attachment_category' );

// Ajax
add_action( 'wp_ajax_query-attachments', 'wp_media_categories_ajax_query_attachments', 0 );

// Some filters and action to process categories
add_action( 'restrict_manage_posts', 'wp_media_categories_restrict_manage_posts' );

// Filter for `no_category` media category attachments list-table requests
add_filter( 'request', 'wp_media_categories_no_category_request' );

// Filter theme-side media category queries
add_action( 'pre_get_posts', 'wp_media_categories_pre_get_posts' );
