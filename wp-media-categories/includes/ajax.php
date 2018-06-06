<?php

/**
 * Media Categories AJAX
 *
 * @package Media/Categories/AJX
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Changing categories in the grid view
 *
 * @since 0.1.0
 */
function wp_media_categories_ajax_query_attachments() {

	// Bail if user cannot upload files
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error();
	}

	// Get names of media taxonomies
	$taxonomies = get_object_taxonomies( 'attachment', 'names' );

	// Look for query
	$query = isset( $_REQUEST[ 'query' ] )
		? (array) $_REQUEST[ 'query' ]
		: array();

	// Default arguments
	$defaults = array(
		's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
		'post_parent', 'post__in', 'post__not_in'
	);

	$query = array_intersect_key( $query, array_flip( array_merge( $defaults, $taxonomies ) ) );

	$query[ 'post_type' ]   = 'attachment';
	$query[ 'post_status' ] = 'inherit';
	if ( current_user_can( get_post_type_object( 'attachment' )->cap->read_private_posts ) ) {
		$query[ 'post_status' ] .= ',private';
	}

	$query[ 'tax_query' ] = array( 'relation' => 'AND' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( isset( $query[ $taxonomy ] ) ) {

			// Filter a specific category
			if ( is_numeric( $query[ $taxonomy ] ) ) {
				array_push( $query[ 'tax_query' ], array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $query[ $taxonomy ]
				) );
			}

			// Filter No category
			if ( $query[ $taxonomy ] == 'no_category' ) {
				$all_terms_ids = wp_media_categories_get_terms_values( 'ids' );
				array_push( $query[ 'tax_query' ], array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $all_terms_ids,
					'operator' => 'NOT IN',
				) );
			}
		}

		unset( $query[ $taxonomy ] );
	}

	$query = apply_filters( 'ajax_query_attachments_args', $query );
	$query = new WP_Query( $query );

	$posts = array_map( 'wp_prepare_attachment_for_js', $query->posts );
	$posts = array_filter( $posts );

	wp_send_json_success( $posts );
}
