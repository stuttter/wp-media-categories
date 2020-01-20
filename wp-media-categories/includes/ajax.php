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


/**
 * Updating categories in a post
 *
 * @since 1.0.2
 */
function wp_media_categories_ajax_update_attachment_taxonomies() {

	if ( ! isset( $_REQUEST['id'] ) ) {
		wp_send_json_error();
	}

	if ( ! $id = absint( $_REQUEST['id'] ) ) {
		wp_send_json_error();
	}

	if ( empty( $_REQUEST['attachments'] ) || empty( $_REQUEST['attachments'][ $id ] ) ) {
		wp_send_json_error();
	}
	$attachment_data = $_REQUEST['attachments'][ $id ];

	check_ajax_referer( 'update-post_' . $id, 'nonce' );

	if ( ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error();
	}

	$post = get_post( $id, ARRAY_A );

	if ( 'attachment' != $post['post_type'] ) {
		wp_send_json_error();
	}

	$post = apply_filters( 'attachment_fields_to_save', $post, $attachment_data );

	wp_update_post( $post );

	$taxonomy = "media_category";

	if ( isset( $attachment_data[ $taxonomy ] ) ) {
		wp_set_object_terms( $id, array_map( 'trim', preg_split( '/,+/', $attachment_data[ $taxonomy ] ) ), $taxonomy, false );
	} else if ( isset($_REQUEST['tax_input']) && isset( $_REQUEST['tax_input'][ $taxonomy ] ) ) {
		wp_set_object_terms( $id, $_REQUEST['tax_input'][ $taxonomy ], $taxonomy, false );
	} else {
		wp_set_object_terms( $id, '', $taxonomy, false );
	}

	if ( ! $attachment = wp_prepare_attachment_for_js( $id ) ) {
		wp_send_json_error();
	}

	wp_send_json_success( $attachment );
}
