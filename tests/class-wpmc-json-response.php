<?php
/**
 * JSON response captured by the media-category test harness.
 *
 * @package WP_Media_Categories
 */

/**
 * Capture a JSON response without terminating the test process.
 */
class WPMC_Json_Response extends RuntimeException {
	/**
	 * Response data.
	 *
	 * @var mixed
	 */
	public $data;

	/**
	 * Store the response data.
	 *
	 * @param mixed $data Response data.
	 */
	public function __construct( $data ) {
		$this->data = $data;
	}
}
