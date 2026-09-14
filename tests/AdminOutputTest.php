<?php

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_add_inline_script' ) ) {
	function wp_add_inline_script() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $value ) {
		return json_encode( $value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
	}
}

final class AdminOutputTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['wpmc_test'] = array();
		$GLOBALS['post_type'] = 'attachment';
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

	public function test_bulk_action_labels_are_json_encoded_for_inline_javascript() {
		$GLOBALS['wpmc_test']['returns']['get_terms'] = array(
			(object) array(
				'term_id' => 7,
				'name'    => '</script><script>alert("bad")</script>',
			),
		);

		wp_media_categories_custom_bulk_admin_footer();

		$this->assertCount( 1, $GLOBALS['wpmc_test']['calls']['wp_add_inline_script'] );
		$this->assertSame( 'jquery-core', $GLOBALS['wpmc_test']['calls']['wp_add_inline_script'][0][0] );
		$this->assertStringNotContainsString( '</script>', $GLOBALS['wpmc_test']['calls']['wp_add_inline_script'][0][1] );
		$this->assertStringContainsString( '\\u003C\\/script\\u003E', $GLOBALS['wpmc_test']['calls']['wp_add_inline_script'][0][1] );
	}
}
