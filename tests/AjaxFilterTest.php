<?php

use PHPUnit\Framework\TestCase;

final class AjaxFilterTest extends TestCase {
	private function reset_test_state() {
		$GLOBALS['wpmc_test'] = array();
	}

	public function test_converts_a_numeric_media_category_filter_into_a_taxonomy_query() {
		$this->reset_test_state();
		$GLOBALS['wpmc_test']['returns']['get_object_taxonomies'] = array( 'media_category' );

		$result = wp_media_categories_ajax_filter_query( array(
			'media_category' => '12',
			'orderby'        => 'date',
		) );

		$this->assertSame( 'date', $result['orderby'] );
		$this->assertArrayNotHasKey( 'media_category', $result );
		$this->assertSame( 'AND', $result['tax_query']['relation'] );
		$this->assertSame( 'media_category', $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( 'id', $result['tax_query'][0]['field'] );
		$this->assertSame( '12', $result['tax_query'][0]['terms'] );
	}

	public function test_converts_no_category_into_a_not_in_query_for_every_term() {
		$this->reset_test_state();
		$GLOBALS['wpmc_test']['returns']['get_object_taxonomies'] = array( 'media_category' );
		$GLOBALS['wpmc_test']['returns']['get_terms'] = array( 3 => 'documents', 8 => 'photographs' );

		$result = wp_media_categories_ajax_filter_query( array( 'media_category' => 'no_category' ) );

		$this->assertSame( array( 3, 8 ), $result['tax_query'][0]['terms'] );
		$this->assertSame( 'NOT IN', $result['tax_query'][0]['operator'] );
		$this->assertArrayNotHasKey( 'media_category', $result );
	}

	/** Confirm the AJAX save filter receives an array and updates term slugs. */
	public function test_ajax_save_passes_a_post_array_to_the_filter_and_updates_terms() {
		$this->reset_test_state();
		$_REQUEST = array(
			'id'          => 7,
			'attachments' => array( 7 => array( 'media_category' => 'photos, artwork' ) ),
		);
		$GLOBALS['wpmc_test']['returns']['current_user_can'] = true;

		$GLOBALS['wpmc_test']['returns']['get_post'] = new WP_Post();

		$GLOBALS['wpmc_test']['returns']['wp_prepare_attachment_for_js'] = array( 'id' => 7 );

		try {
			wp_media_categories_ajax_update_attachment_taxonomies();
			$this->fail( 'Expected a JSON response.' );
		} catch ( WPMC_Json_Response $response ) {
			$this->assertSame( array( 'id' => 7 ), $response->data );
		}

		$this->assertSame( 'attachment', $GLOBALS['wpmc_test']['calls']['wp_update_post'][0][0]['post_type'] );
		$this->assertSame( array( 'photos', 'artwork' ), $GLOBALS['wpmc_test']['calls']['wp_set_object_terms'][0][1] );
	}

	/** Confirm the AJAX query excludes false attachment preparations. */
	public function test_ajax_query_keeps_only_prepared_attachment_arrays() {
		$this->reset_test_state();
		$_REQUEST = array(
			'query' => array(
				's'       => 'photo',
				'ignored' => 'value',
			),
		);
		$GLOBALS['wpmc_test']['returns']['current_user_can'] = true;

		$GLOBALS['wpmc_test']['returns']['get_object_taxonomies'] = array( 'media_category' );

		$GLOBALS['wpmc_test']['query_posts'] = array( 7, 8 );

		$GLOBALS['wpmc_test']['callbacks']['wp_prepare_attachment_for_js'] = static function ( $id ) {
			return 7 === $id ? array( 'id' => 7 ) : false;
		};

		try {
			wp_media_categories_ajax_query_attachments();
			$this->fail( 'Expected a JSON response.' );
		} catch ( WPMC_Json_Response $response ) {
			$this->assertSame( array( 0 => array( 'id' => 7 ) ), $response->data['posts'] );
		}

		$query = $GLOBALS['wpmc_test']['calls']['WP_Query'][0][0];
		$this->assertSame( 'photo', $query['s'] );
		$this->assertArrayNotHasKey( 'ignored', $query );
	}
}
