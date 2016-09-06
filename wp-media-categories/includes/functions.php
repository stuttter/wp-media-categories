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
function wp_media_categories_update_count_callback( $terms = array(), $media_taxonomy = 'category' ) {
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

	$prepared = $wpdb->prepare( $sql, 'media_category', 'media_category' );
	$count    = $wpdb->get_results( $prepared );

	// update all count values from taxonomy
	foreach ( $count as $row_count ) {
		$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $row_count->total ), array( 'term_taxonomy_id' => $row_count->term_taxonomy_id ) );
	}
}

/**
 * Add values to query vars to extend the query
 *
 * @since 0.1.0
 */
function wp_media_categories_query_vars_add_values( $query_vars = '', $values_to_add = '' ) {

	// Make input into array
	$new_query_vars    = $query_vars;
	$new_values_to_add = $values_to_add;

	if ( ! is_array( $query_vars ) ) {
		$new_query_vars = array( $query_vars );
	}

	if ( ! is_array( $values_to_add ) ) {
		$new_values_to_add = array( $values_to_add );
	}

	// Merge inputs to return
	return array_merge( $new_query_vars, $new_values_to_add );
}

/**
 * Get posts for media taxonomy
 *
 * @since 0.1.0
 *
 * @global WPDB $wpdb
 *
 * @param  string $taxonomy
 *
 * @return array
 */
function wp_media_categories_get_posts_for_media_taxonomy( $taxonomy = '' ) {
	global $wpdb;

	// Validate input
	if ( empty( $taxonomy ) ) {
		return array();
	}

	// Get the terms for this taxonomy
	$sql     = "SELECT * FROM {$wpdb->term_taxonomy} AS tt WHERE tt.taxonomy = %s";
	$prepare = $wpdb->prepare( $sql, $taxonomy );
	$terms   = $wpdb->get_results( $prepare );

	// Validate $terms found
	if ( is_wp_error( $terms ) || (count( $terms ) == 0 ) ) {
		return array();
	}

	// Create a list of taxonomyTermIDs to be used for the query
	$term_ids = array();
	foreach ( $terms as $term ) {
		$term_ids[] = $term->term_taxonomy_id;
	}
	$term_ids = implode( ',', $term_ids );

	$query  = "SELECT $wpdb->posts.* FROM $wpdb->posts ";
	$query .= " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) ";
	$query .= " WHERE 1=1 ";
	$query .= "   AND $wpdb->posts.post_type = 'attachment' ";
	$query .= "   AND ($wpdb->term_relationships.term_taxonomy_id IN ($term_ids)) ";
	$query .= " GROUP BY $wpdb->posts.ID";

	$taxonomyPosts = $wpdb->get_results( $query );

	return $taxonomyPosts;

}

/**
 * Get the options to determine the list of media_category
 *
 * @since 0.1.0
 */
function wp_media_categories_get_media_category_options( $selected_value = '') {
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
 * Implement the request to filter media without category
 *
 * @since 0.1.0
 */
function wp_media_categories_request( $query_args = array() ) {

	// No category?
	$media_category = wp_media_categories_get_no_category_search();

	// Cuz I'm searchin...
	if ( ! empty( $media_category ) ) {

		// Find all posts for the current mediaCategory to use for filtering them out
		$all_attachments = wp_media_categories_get_posts_for_media_taxonomy( $media_category );

		// Post not in?
		$post_not_in = array();
		foreach ( $all_attachments as $key => $val) {
			$post_not_in[] = $val->ID;
		}
		$query_args['post__not_in'] = $post_not_in;

		// Reset the search query parameters to search for all attachments
		$query_args[ $media_category ] = 0;
	}

	return $query_args;
}

/**
 * Check whether this search is for NO Category
 *
 * @since 0.1.0
 */
function wp_media_categories_get_no_category_search() {

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

	// Only perform search on non-admin pages
	if ( is_admin() ) {
		return;
	}

	// Check whether this is the main query
	if ( ! $query->is_main_query() ) {
		return;
	}

	// Handle query if it is used for media is_archive
	if ( is_archive() ) {

		// Get media taxonomy and categories to find
		$media_categories = $query->get( 'media_category', '__not_found' );

		// Check categories to find
		if ( '__not_found' !== $media_categories ) {
			$query->set( 'post_type',   'attachment' );
			$query->set( 'post_status', 'inherit'    );
		}
	}

	// Add media for search when desired
	if ( is_search() ) {

		// Add attachment to post_type
		$query_post_type = $query->get( 'post_type', '__not_found' );
		if ( '__not_found' !== $query_post_type ) {
			$query_post_type = 'post';
		}

		$query_post_type = wp_media_categories_query_vars_add_values( $query_post_type, 'attachment' );
		$query->set( 'post_type', $query_post_type);

		// Add inherit to post_status
		$query_post_status = $query->get( 'post_status', '__not_found' );
		if ( '__not_found' !== $query_post_status ) {
			$query_post_status = 'publish';
		}

		$query_post_status = wp_media_categories_query_vars_add_values( $query_post_status, 'inherit' );
		$query->set( 'post_status', $query_post_status );
	}
}
