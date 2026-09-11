<?php

use PHPUnit\Framework\TestCase;

final class UpdateCountTest extends TestCase {
	private function reset_test_state() {
		$GLOBALS['wpmc_test'] = array();
	}

	public function test_updates_every_returned_taxonomy_count() {
		$this->reset_test_state();
		$database = new WP_Media_Categories_Test_Database();
		$database->results = array(
			(object) array( 'term_taxonomy_id' => 7, 'total' => 3 ),
			(object) array( 'term_taxonomy_id' => 8, 'total' => 0 ),
		);
		$GLOBALS['wpdb'] = $database;

		wp_media_categories_update_count_callback( array( 7, 8 ), (object) array( 'name' => 'photograph_type' ) );

		$this->assertSame( array( 'photograph_type', 'photograph_type' ), $database->prepared_arguments );
		$this->assertCount( 2, $database->updates );
		$this->assertSame( array( 'count' => 3 ), $database->updates[0][1] );
		$this->assertSame( array( 'term_taxonomy_id' => 8 ), $database->updates[1][2] );
	}

	public function test_defaults_to_the_media_category_taxonomy() {
		$this->reset_test_state();
		$database = new WP_Media_Categories_Test_Database();
		$GLOBALS['wpdb'] = $database;

		wp_media_categories_update_count_callback();

		$this->assertSame( array( 'media_category', 'media_category' ), $database->prepared_arguments );
	}
}

final class WP_Media_Categories_Test_Database {
	public $term_relationships = 'wp_term_relationships';
	public $term_taxonomy = 'wp_term_taxonomy';
	public $prepared_arguments = array();
	public $results = array();
	public $updates = array();

	public function prepare( $query, $first, $second ) {
		$this->prepared_arguments = array( $first, $second );
		return $query;
	}

	public function get_results() {
		return $this->results;
	}

	public function update() {
		$this->updates[] = func_get_args();
	}
}
