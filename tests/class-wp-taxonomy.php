<?php
/**
 * Minimal taxonomy fixture for media-category tests.
 *
 * @package WP_Media_Categories
 */

/**
 * Taxonomy rewrite fixture.
 */
class WP_Taxonomy {
	/**
	 * Rewrite arguments.
	 *
	 * @var array<string, string>
	 */
	public $rewrite;

	/**
	 * Configure the rewrite slug.
	 *
	 * @param string $slug Rewrite slug.
	 */
	public function __construct( $slug ) {
		$this->rewrite = array( 'slug' => $slug );
	}
}
