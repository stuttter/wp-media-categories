<?php

use PHPUnit\Framework\TestCase;

final class BulkActionTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['wpmc_test'] = array();
		$_REQUEST             = array(
			'action'       => 'bulk_toggle',
			'bulk_tax_cat' => 'media_category',
			'bulk_tax_id'  => '7',
			'media'        => array( '42' ),
		);

		$GLOBALS['wpmc_test']['callbacks']['check_admin_referer'] = function ( $action ) {
			if ( 'bulk-media' !== $action || 'valid-bulk-media-nonce' !== ( $_REQUEST['_wpnonce'] ?? '' ) ) {
				throw new UnexpectedValueException( 'Invalid bulk-media nonce.' );
			}

			return 1;
		};
	}

	protected function tearDown(): void {
		$_REQUEST = array();
	}

	/**
	 * @dataProvider invalid_nonce_provider
	 */
	public function test_rejects_invalid_bulk_action_nonces_before_mutation( $nonce ) {
		if ( null !== $nonce ) {
			$_REQUEST['_wpnonce'] = $nonce;
		}

		try {
			wp_media_categories_custom_bulk_action();
			$this->fail( 'The bulk action accepted an invalid nonce.' );
		} catch ( UnexpectedValueException $exception ) {
			$this->assertSame( 'Invalid bulk-media nonce.', $exception->getMessage() );
		}

		$this->assertSame( array( array( 'bulk-media' ) ), $GLOBALS['wpmc_test']['calls']['check_admin_referer'] );
		$this->assertArrayNotHasKey( 'has_term', $GLOBALS['wpmc_test']['calls'] );
		$this->assertArrayNotHasKey( 'wp_remove_object_terms', $GLOBALS['wpmc_test']['calls'] );
		$this->assertArrayNotHasKey( 'wp_set_object_terms', $GLOBALS['wpmc_test']['calls'] );
	}

	public function invalid_nonce_provider() {
		return array(
			'missing nonce' => array( null ),
			'invalid nonce' => array( 'invalid' ),
		);
	}

	public function test_valid_core_bulk_media_nonce_reaches_existing_mutation_logic() {
		$_REQUEST['_wpnonce'] = 'valid-bulk-media-nonce';
		$GLOBALS['wpmc_test']['returns']['current_user_can'] = true;
		$GLOBALS['wpmc_test']['returns']['has_term']         = false;

		$GLOBALS['wpmc_test']['callbacks']['wp_set_object_terms'] = function () {
			throw new RuntimeException( 'Mutation reached.' );
		};

		try {
			wp_media_categories_custom_bulk_action();
			$this->fail( 'The valid bulk action did not reach the mutation.' );
		} catch ( RuntimeException $exception ) {
			$this->assertSame( 'Mutation reached.', $exception->getMessage() );
		}

		$this->assertSame( array( array( 'bulk-media' ) ), $GLOBALS['wpmc_test']['calls']['check_admin_referer'] );
		$this->assertSame( array( array( 'edit_post', 42 ) ), $GLOBALS['wpmc_test']['calls']['current_user_can'] );
		$this->assertSame( array( array( 7, 'media_category', 42 ) ), $GLOBALS['wpmc_test']['calls']['has_term'] );
		$this->assertSame( array( array( 42, 7, 'media_category', true ) ), $GLOBALS['wpmc_test']['calls']['wp_set_object_terms'] );
		$this->assertArrayNotHasKey( 'wp_remove_object_terms', $GLOBALS['wpmc_test']['calls'] );
	}
}
