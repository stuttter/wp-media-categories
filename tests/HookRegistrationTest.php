<?php

use PHPUnit\Framework\TestCase;

final class HookRegistrationTest extends TestCase {
	public function test_registers_the_public_hook_contract() {
		$GLOBALS['wpmc_test'] = array();
		require dirname( __DIR__ ) . '/wp-media-categories/includes/hooks.php';

		$actions = $GLOBALS['wpmc_test']['calls']['add_action'];
		$filters = $GLOBALS['wpmc_test']['calls']['add_filter'];
		$shortcodes = $GLOBALS['wpmc_test']['calls']['add_shortcode'];

		$this->assertContains( array( 'init', 'wp_media_categories_register_media_taxonomy' ), $actions );
		$this->assertContains( array( 'admin_enqueue_scripts', 'wp_media_categories_custom_bulk_admin_footer', 20 ), $actions );
		$this->assertNotContains( array( 'admin_footer-upload.php', 'wp_media_categories_custom_bulk_admin_footer' ), $actions );
		$this->assertContains( array( 'wp_ajax_save-attachment-compat', 'wp_media_categories_ajax_update_attachment_taxonomies', 0 ), $actions );
		$this->assertContains( array( 'ajax_query_attachments_args', 'wp_media_categories_ajax_filter_query' ), $filters );
		$this->assertContains( array( 'request', 'wp_media_categories_no_category_request' ), $filters );
		$this->assertSame( array( array( 'mc-gallery', 'wp_media_categories_register_gallery_shortcode' ) ), $shortcodes );
	}
}
