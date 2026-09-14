<?php

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_localize_script' ) ) {
	function wp_localize_script() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_media_categories_get_plugin_url' ) ) {
	function wp_media_categories_get_plugin_url() {
		return 'https://example.org/wp-content/plugins/wp-media-categories/wp-media-categories/';
	}
}

if ( ! function_exists( 'wp_media_categories_get_asset_version' ) ) {
	function wp_media_categories_get_asset_version() {
		return 202005130001;
	}
}

final class AdminOutputTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['wpmc_test'] = array();
		$GLOBALS['pagenow']   = 'upload.php';
	}

	public function test_requests_media_terms_with_the_current_get_terms_signature() {
		$GLOBALS['wpmc_test']['returns']['get_terms'] = array( 7 => 'photographs' );

		$this->assertSame( array( 7 ), wp_media_categories_get_terms_values() );
		$this->assertSame(
			array(
				array(
					array(
						'taxonomy'   => 'media_category',
						'hide_empty' => 0,
						'fields'     => 'id=>slug',
					),
				),
			),
			$GLOBALS['wpmc_test']['calls']['get_terms']
		);
	}

	public function test_bulk_actions_are_registered_during_enqueue_for_footer_output() {
		$GLOBALS['wpmc_test']['returns']['get_terms'] = array(
			(object) array(
				'term_id' => 7,
				'name'    => '</script><script>alert("bad")</script>',
			),
		);

		wp_media_categories_custom_bulk_admin_footer();

		$this->assertSame(
			array(
				'wp-media-categories-bulk-actions',
				'https://example.org/wp-content/plugins/wp-media-categories/wp-media-categories/assets/js/bulk-actions.js',
				array( 'jquery' ),
				202005130001,
				true,
			),
			$GLOBALS['wpmc_test']['calls']['wp_enqueue_script'][0]
		);
		$this->assertSame( 'wp-media-categories-bulk-actions', $GLOBALS['wpmc_test']['calls']['wp_localize_script'][0][0] );
		$this->assertSame( 'wpMediaCategoriesBulkActions', $GLOBALS['wpmc_test']['calls']['wp_localize_script'][0][1] );
		$this->assertSame( 'media_category', $GLOBALS['wpmc_test']['calls']['wp_localize_script'][0][2]['taxonomy'] );
		$this->assertSame(
			array( '7' => 'Toggle </script><script>alert("bad")</script>' ),
			$GLOBALS['wpmc_test']['calls']['wp_localize_script'][0][2]['actions']
		);
	}

	public function test_bulk_actions_are_not_enqueued_outside_the_media_library() {
		$GLOBALS['pagenow'] = 'post.php';

		wp_media_categories_custom_bulk_admin_footer();

		$calls = isset( $GLOBALS['wpmc_test']['calls'] ) ? $GLOBALS['wpmc_test']['calls'] : array();

		$this->assertArrayNotHasKey( 'get_terms', $calls );
		$this->assertArrayNotHasKey( 'wp_enqueue_script', $calls );
	}
}
