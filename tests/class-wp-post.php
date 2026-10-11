<?php
/**
 * Minimal post fixture for media-category tests.
 *
 * @package WP_Media_Categories
 */

/**
 * Attachment post fixture.
 */
class WP_Post {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	public $post_type = 'attachment';

	/**
	 * Return the array shape WordPress supplies to save filters.
	 *
	 * @return array<string, string>
	 */
	public function to_array() {
		return array( 'post_type' => $this->post_type );
	}
}
