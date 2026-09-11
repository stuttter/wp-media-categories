<?php

use PHPUnit\Framework\TestCase;

final class TaxonomyTest extends TestCase {
	private function reset_test_state() {
		$GLOBALS['wpmc_test'] = array();
		$_REQUEST = array();
	}

	public function test_registers_the_attachment_taxonomy_contract() {
		$this->reset_test_state();
		wp_media_categories_register_media_taxonomy();

		$this->assertSame( 'media_category', $GLOBALS['wpmc_test']['calls']['register_taxonomy'][0][0] );
		$this->assertSame( array( 'attachment' ), $GLOBALS['wpmc_test']['calls']['register_taxonomy'][0][1] );

		$args = $GLOBALS['wpmc_test']['calls']['register_taxonomy'][0][2];
		$this->assertTrue( $args['hierarchical'] );
		$this->assertTrue( $args['show_ui'] );
		$this->assertTrue( $args['show_admin_column'] );
		$this->assertSame( 'wp_media_categories_update_count_callback', $args['update_count_callback'] );
		$this->assertSame( 'media-category', $args['rewrite']['slug'] );
	}

	public function test_builds_the_media_category_dropdown_contract() {
		$this->reset_test_state();
		$options = wp_media_categories_get_media_category_options( 'photographs' );

		$this->assertSame( 'media_category', $options['taxonomy'] );
		$this->assertSame( 'no_category', $options['option_none_value'] );
		$this->assertSame( 'photographs', $options['selected'] );
		$this->assertSame( 'slug', $options['value'] );
		$this->assertInstanceOf( WP_Media_Categories_Filter_Walker::class, $options['walker'] );
	}

	public function test_converts_the_no_category_filter_into_a_not_exists_query() {
		$this->reset_test_state();
		$GLOBALS['wpmc_test']['returns']['is_admin']      = true;
		$GLOBALS['wpmc_test']['returns']['is_main_query'] = true;
		$_REQUEST = array(
			'filter_action'  => 'Filter',
			'bulk_tax_cat'   => 'media_category',
			'media_category' => 'no_category',
		);

		$result = wp_media_categories_no_category_request( array( 'media_category' => 'no_category' ) );

		$this->assertTrue( $result['suppress_filters'] );
		$this->assertNull( $result['media_category'] );
		$this->assertSame( 'media_category', $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( 'NOT EXISTS', $result['tax_query'][0]['operator'] );
		$this->assertFalse( $result['tax_query'][0]['include_children'] );
	}

	public function test_leaves_non_admin_requests_unchanged() {
		$this->reset_test_state();
		$GLOBALS['wpmc_test']['returns']['is_admin'] = false;
		$query = array( 'media_category' => 'no_category' );

		$this->assertSame( $query, wp_media_categories_no_category_request( $query ) );
	}
}
