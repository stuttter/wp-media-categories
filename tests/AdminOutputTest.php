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

if ( ! function_exists( 'wp_add_inline_script' ) ) {
	function wp_add_inline_script() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_script_is' ) ) {
	function wp_script_is() {
		return (bool) wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_dropdown_categories' ) ) {
	function wp_dropdown_categories() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $value ) {
		return strtolower( $value );
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'wp_get_referer' ) ) {
	function wp_get_referer() {
		return 'https://example.org/wp-admin/upload.php';
	}
}

if ( ! function_exists( 'remove_query_arg' ) ) {
	function remove_query_arg( $keys, $url ) {
		return $url;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path ) {
		return 'https://example.org/wp-admin/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( $key, $value, $url = '' ) {
		$args = is_array( $key ) ? $key : array( $key => $value );
		return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args );
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
		$_GET                  = array();
		$_REQUEST              = array();
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

	public function test_media_grid_data_handles_dropdown_whitespace_and_hex_encodes_inline_json() {
		$GLOBALS['wpmc_test']['returns']['wp_script_is'] = true;
		$GLOBALS['wpmc_test']['returns']['wp_dropdown_categories'] = "<select>\n,{\"term_id\":\"7\",\"term_name\":\"Photos \\u003C\\/script\\u003E\"}\n</select>";

		wp_media_categories_enqueue_admin_scripts();

		$inline_script = $GLOBALS['wpmc_test']['calls']['wp_add_inline_script'][0][1];
		$this->assertStringContainsString( '"term_id":"7"', $inline_script );
		$this->assertStringContainsString( '\\u003C\\/script\\u003E', $inline_script );
		$this->assertStringNotContainsString( '</script>', $inline_script );
	}

	public function test_preserves_percent_encoded_term_slugs_in_filters_and_sendback_urls() {
		$_GET['term'] = '%e4%b8%ad';

		ob_start();
		wp_media_categories_add_category_filter();
		ob_end_clean();

		$dropdown_options = $GLOBALS['wpmc_test']['calls']['wp_dropdown_categories'][0][0];
		$this->assertSame( '%e4%b8%ad', $dropdown_options['selected'] );

		$_REQUEST['media_category'] = '%e4%b8%ad';
		$sendback = wp_media_categories_create_sendback_url();
		parse_str( parse_url( $sendback, PHP_URL_QUERY ), $query_args );

		$this->assertSame( '%e4%b8%ad', $query_args['media_category'] );
	}
}
