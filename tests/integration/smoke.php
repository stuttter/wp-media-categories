<?php

/**
 * Exercise the public attachment-taxonomy contract in a real WordPress site.
 *
 * This file is loaded by the centrally maintained integration runner after the
 * production plugin build has been activated.
 *
 * @package WP_Media_CategoriesTests
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fail the smoke check with a useful diagnostic.
 *
 * @param bool   $condition Whether the expected behavior was observed.
 * @param string $message   Failure diagnostic.
 * @return void
 */
function wp_media_categories_smoke_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

wp_media_categories_smoke_assert( ! is_multisite(), 'WP Media Categories must use the single-site pilot.' );
wp_media_categories_smoke_assert( function_exists( '_wp_media_categories' ), 'The production plugin did not load.' );
wp_media_categories_smoke_assert( taxonomy_exists( 'media_category' ), 'The media_category taxonomy was not registered.' );
wp_media_categories_smoke_assert( shortcode_exists( 'mc-gallery' ), 'The mc-gallery shortcode was not registered.' );
wp_media_categories_smoke_assert( 10 === has_action( 'pre_get_posts', 'wp_media_categories_pre_get_posts' ), 'The front-end media query hook was not registered.' );

$taxonomy = get_taxonomy( 'media_category' );
wp_media_categories_smoke_assert( $taxonomy instanceof WP_Taxonomy, 'WordPress did not return the media_category taxonomy object.' );
wp_media_categories_smoke_assert( in_array( 'attachment', $taxonomy->object_type, true ), 'media_category does not target attachments.' );
wp_media_categories_smoke_assert( true === $taxonomy->hierarchical, 'media_category must remain hierarchical.' );
wp_media_categories_smoke_assert( true === $taxonomy->public, 'media_category must remain public.' );
wp_media_categories_smoke_assert( true === $taxonomy->show_ui, 'media_category must retain its administration UI.' );
wp_media_categories_smoke_assert( 'wp_media_categories_update_count_callback' === $taxonomy->update_count_callback, 'media_category lost its custom count callback.' );
wp_media_categories_smoke_assert( 'media-category' === $taxonomy->rewrite['slug'], 'media_category rewrite behavior changed.' );

$term_id       = 0;
$attachment_id = 0;
$suffix        = strtolower( wp_generate_uuid4() );

try {
	$term = wp_insert_term(
		'Portfolio smoke ' . $suffix,
		'media_category',
		array( 'slug' => 'portfolio-smoke-' . $suffix )
	);
	wp_media_categories_smoke_assert( ! is_wp_error( $term ), 'WordPress could not create a media category: ' . ( is_wp_error( $term ) ? $term->get_error_message() : '' ) );
	$term_id = (int) $term['term_id'];

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_status'    => 'inherit',
			'post_title'     => 'Portfolio smoke attachment ' . $suffix,
		),
		false,
		0,
		true
	);
	wp_media_categories_smoke_assert( ! is_wp_error( $attachment_id ) && 0 < $attachment_id, 'WordPress could not create a smoke-test attachment.' );
	$attachment_id = (int) $attachment_id;

	$relationships = wp_set_object_terms( $attachment_id, array( $term_id ), 'media_category', false );
	wp_media_categories_smoke_assert( ! is_wp_error( $relationships ) && 1 === count( $relationships ), 'WordPress could not assign the media category.' );

	$assigned = wp_get_object_terms( $attachment_id, 'media_category', array( 'fields' => 'ids' ) );
	wp_media_categories_smoke_assert( ! is_wp_error( $assigned ) && array( $term_id ) === array_map( 'intval', $assigned ), 'The attachment taxonomy relationship was not persisted.' );

	$stored_term = get_term( $term_id, 'media_category' );
	wp_media_categories_smoke_assert( $stored_term instanceof WP_Term && 1 === (int) $stored_term->count, 'The custom attachment count callback did not store the expected count.' );

	$query = new WP_Query(
		array(
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'field'    => 'term_id',
					'taxonomy' => 'media_category',
					'terms'    => array( $term_id ),
				),
			),
		)
	);
	wp_media_categories_smoke_assert( array( $attachment_id ) === array_map( 'intval', $query->posts ), 'A real attachment query did not honor the media category relationship.' );
} finally {
	if ( 0 < $attachment_id ) {
		wp_delete_attachment( $attachment_id, true );
	}
	if ( 0 < $term_id ) {
		wp_delete_term( $term_id, 'media_category' );
	}
}
