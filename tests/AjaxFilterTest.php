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
}
