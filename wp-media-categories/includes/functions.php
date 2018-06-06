<?php

/**
 * Media Categories Functions
 *
 * @package Media/Categories/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Custom update_count_callback
 *
 * @since 0.1.0
 */
function wp_media_categories_update_count_callback( $terms = array(), $media_taxonomy = 'media_category' ) {
	global $wpdb;

	// select id & count from taxonomy
	$sql = "SELECT term_taxonomy_id, MAX(total) AS total FROM ((
				SELECT tt.term_taxonomy_id, COUNT(*) AS total
					FROM {$wpdb->term_relationships} tr, {$wpdb->term_taxonomy} tt
					WHERE tr.term_taxonomy_id = tt.term_taxonomy_id
						AND tt.taxonomy = %s
					GROUP BY tt.term_taxonomy_id
				) UNION ALL (
					SELECT term_taxonomy_id, 0 AS total
						FROM {$wpdb->term_taxonomy}
						WHERE taxonomy = %s
				)) AS unioncount GROUP BY term_taxonomy_id";

	$prepared = $wpdb->prepare( $sql, $media_taxonomy->name, $media_taxonomy->name );
	$count    = $wpdb->get_results( $prepared );

	// update all count values from taxonomy
	foreach ( $count as $row_count ) {
		$wpdb->update(
			$wpdb->term_taxonomy,
			array( 'count'            => $row_count->total            ),
			array( 'term_taxonomy_id' => $row_count->term_taxonomy_id )
		);
	}
}

/**
 * Get the options to determine the list of media_category
 *
 * @since 0.1.0
 */
function wp_media_categories_get_media_category_options( $selected_value = '' ) {
	return array(
		'taxonomy'           => 'media_category',
		'name'               => 'media_category',
		'option_none_value'  => 'no_category',
		'selected'           => $selected_value,
		'hide_empty'         => false,
		'hierarchical'       => true,
		'orderby'            => 'name',
		'walker'             => new WP_Media_Categories_Filter_Walker(),
		'show_option_all'    => __( 'All categories', 'wp-media-categories' ),
		'show_option_none'   => __( 'No categories',  'wp-media-categories' ),
		'show_count'         => true,
		'value'              => 'slug'
	);
}

/**
 * Manipulate the request to filter media without category
 *
 * @since 1.0.1
 */
function wp_media_categories_no_category_request( $query_args = array() ) {

	// Bail if not in admin
	if ( ! is_admin() || ! is_main_query() ) {
		return $query_args;
	}

	// No category?
	$media_category = wp_media_categories_get_no_category_search();

	// Cuz I'm searchin...
	if ( ! empty( $media_category ) && ! empty( $query_args[ $media_category ] ) ) {

		// No categories, so do a "NOT EXISTS" taxonomy query
		if ( 'no_category' === $query_args[ $media_category ] ) {

			// This is necessary to prevent the JOIN clause from being stomped
			// and replaced for postmeta
			$query_args['suppress_filters'] = true;

			// This adds a taxonomy query, looking for no terms
			$query_args['tax_query'] = array(
				array(
					'taxonomy'         => $media_category,
					'operator'         => 'NOT EXISTS',
					'include_children' => false
				)
			);

			// Nullify the query argument to prevent incorrect core assertions
			$query_args[ $media_category ] = null;
		}
	}

	return $query_args;
}

/**
 * Check whether this search is for NO Category
 *
 * @since 0.1.0
 */
function wp_media_categories_get_no_category_search() {

	// Default return value
	$search = '';

	// Check for correct Filter situation
	if ( empty( $_REQUEST['filter_action'] ) ) {
		return $search;
	}

	// Check parameters to use for new request
	if ( ! empty( $_REQUEST['bulk_tax_cat'] ) ) {
		$search = $_REQUEST['bulk_tax_cat'];

		// Get the request value
		$request = isset( $_REQUEST[ $search ] )
			? $_REQUEST[ $search ]
			: '';

		// Filter request on specific category so don't mess with it
		if ( 'no_category' === $request ) {
			return $search;
		}
	}

	return '';
}

/**
 * Fired when the plugin is activated.
 *
 * @since  0.1.0
 *
 * @param  WP_Query $query The query object used to find objects like posts
 */
function wp_media_categories_pre_get_posts( WP_Query $query ) {

	// Bail if in admin
	if ( is_admin() ) {
		return;
	}

	// Bail if not main query
	if ( ! $query->is_main_query() ) {
		return;
	}

	// Bail if not media_category query
	if ( ! is_tax( 'media_category' ) ) {
		return;
	}

	// Looking at some kind of media_category term archive
	if ( is_archive() ) {

		// Get media taxonomy and categories to find, default to __not_found
		$media_categories = $query->get( 'media_category', '__not_found' );

		// Looking at a specific media category
		if ( '__not_found' !== $media_categories ) {
			$query->set( 'post_type',   'attachment' );
			$query->set( 'post_status', 'inherit'    );
		}
	}
}
