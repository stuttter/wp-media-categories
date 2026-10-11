<?php

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $value ) {
		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_js' ) ) {
	function esc_js( $value ) {
		return addslashes( $value );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'wp_dropdown_categories' ) ) {
	function wp_dropdown_categories() {
		return wpmc_test_call( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'get_taxonomy' ) ) {
	function get_taxonomy() {
		return $GLOBALS['wpmc_test']['taxonomy'] ?? new WP_Taxonomy( 'media-category' );
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.org/?first=one&second=two/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $value ) {
		return rtrim( $value, '/' ) . '/';
	}
}

require_once dirname( __DIR__ ) . '/wp-media-categories/includes/widgets.php';

final class WidgetOutputTest extends TestCase {
	/** Confirm dropdown links use the registered taxonomy rewrite slug. */
	public function test_dropdown_uses_custom_taxonomy_rewrite_slug() {
		$GLOBALS['wpmc_test']['taxonomy'] = new WP_Taxonomy( 'custom-media' );
		$buffer_level                     = ob_get_level();

		try {
			$widget = new WP_Media_Categories();

			ob_start();
			$widget->widget(
				array(
					'before_widget' => '<section>',
					'after_widget'  => '</section>',
					'before_title'  => '<h2>',
					'after_title'   => '</h2>',
				),
				array( 'dropdown' => 1 )
			);
			$output = ob_get_clean();

			$this->assertStringContainsString( 'custom-media\\/', $output );
		} finally {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			unset( $GLOBALS['wpmc_test']['taxonomy'] );
		}
	}

	/** Confirm dropdown links retain the registered default rewrite slug. */
	public function test_dropdown_uses_default_slug_without_taxonomy_rewrite_data() {
		$GLOBALS['wpmc_test']['taxonomy'] = false;
		$buffer_level                     = ob_get_level();

		try {
			$widget = new WP_Media_Categories();

			ob_start();
			$widget->widget(
				array(
					'before_widget' => '<section>',
					'after_widget'  => '</section>',
					'before_title'  => '<h2>',
					'after_title'   => '</h2>',
				),
				array( 'dropdown' => 1 )
			);
			$output = ob_get_clean();

			$this->assertStringContainsString( 'media-category\\/', $output );
		} finally {
			while ( ob_get_level() > $buffer_level ) {
				ob_end_clean();
			}
			unset( $GLOBALS['wpmc_test']['taxonomy'] );
		}
	}

	public function test_dropdown_redirect_url_is_json_encoded_for_javascript() {
		$widget         = new WP_Media_Categories();
		$widget->id_base = 'wp_media_categories_categories';
		$widget->number  = 2;

		ob_start();
		$widget->widget(
			array(
				'before_widget' => '<section>',
				'after_widget'  => '</section>',
				'before_title'  => '<h2>',
				'after_title'   => '</h2>',
			),
			array(
				'title'    => 'Media',
				'dropdown' => 1,
			)
		);
		$output = ob_get_clean();

		$this->assertStringContainsString( 'first=one\\u0026second=two', $output );
		$this->assertStringNotContainsString( '&#038;', $output );
	}
}
