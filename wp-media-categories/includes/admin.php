<?php

/**
 * Media Categories Admin
 *
 * @package Media/Categories/Admin
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue admin scripts and styles
 */
function wp_media_categories_enqueue_admin_scripts() {
	global $pagenow;

	// Asset management
	$url = wp_media_categories_get_plugin_url();
	$ver = wp_media_categories_get_asset_version();

	// Media editor
	if ( wp_script_is( 'media-editor' ) && ( ( 'upload.php' == $pagenow ) || ( 'post.php' == $pagenow ) || ( 'post-new.php' == $pagenow ) ) ) {

		// Dropdown
		$attachment_terms = wp_dropdown_categories( array(
			'taxonomy'        => 'media_category',
			'hide_empty'      => false,
			'hierarchical'    => true,
			'orderby'         => 'name',
			'show_count'      => true,
			'walker'          => new WP_Media_Categories_Media_Grid_Walker(),
			'value'           => 'id',
			'echo'            => false
		) );

		// No select
		$attachment_terms = preg_replace( array( '/<select([^>]*)>/', '/<\/select>/' ), '', $attachment_terms );

		// Add an attachment_terms for No category
		$no_categories  = __( 'No categories',  'wp-media-categories' );
		$all_categories = __( 'All categories', 'wp-media-categories' );
		$no_category_term = ' ,{"term_id":"' . 'no_category' . '","term_name":"' . $no_categories . '"}';
		$attachment_terms = $no_category_term . substr( $attachment_terms, 1 );

		echo '<script type="text/javascript">';
		echo '/* <![CDATA[ */';
		echo 'var wp_media_categories_taxonomies = {"' . 'media_category' . '":';
		echo     '{"list_title":"' . html_entity_decode( $all_categories, ENT_QUOTES, 'UTF-8' ) . '",';
		echo       '"term_list":[' . substr( $attachment_terms, 2 ) . ']}};';
		echo '/* ]]> */';
		echo '</script>';

		// Script
		wp_enqueue_script( 'wp-media-categories-media-views', $url . 'assets/js/media-views.js', array( 'media-views' ), $ver, true );
	}

	// Styling
	wp_enqueue_style( 'wp-media-categories-styling', $url . 'assets/css/admin.css', array(), $ver );
}

/**
 * Add a category filter
 *
 * @since 0.1.0
 */
function wp_media_categories_add_category_filter() {
	global $pagenow;

	// Bail if not upload page
	if ( 'upload.php' !== $pagenow ) {
		return;
	}

	// Looking at specific term
	$selected_value = isset( $_GET[ 'term' ] )
		? $_GET[ 'term' ]
		: '';

	// Maybe looking for attachments with no terms
	if ( empty( $selected_value ) ) {
		$selected_value = isset( $_GET[ 'media_category' ] )
			? $_GET[ 'media_category' ]
			: '';
	} ?>

	<label for="media_category" class="screen-reader-text"><?php esc_html_e( 'Filter by Category', 'wp-media-categories' ); ?></label>

	<?php

	$dropdown_options = wp_media_categories_get_media_category_options( $selected_value );

	wp_dropdown_categories( $dropdown_options );
}

/**
 * Add a filter for restrict_manage_posts for categories
 *
 * @since 0.1.0
 */
function wp_media_categories_restrict_manage_posts() {
	wp_media_categories_add_category_filter();
}

/**
 * Return URL to send the request back to
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_media_categories_create_sendback_url() {

	// Create a sendback url to report the results
	$sendback = remove_query_arg( array( 'exported', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
	if ( empty( $sendback ) || ( false === strpos( wp_get_referer(), 'upload.php' ) ) ) {
		$sendback = admin_url( "upload.php" );
	}

	// Remove some superfluous arguments
	$sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

	// Pagenumber
	$pagenum  = isset( $_REQUEST[ 'paged' ] ) ? absint( $_REQUEST[ 'paged' ] ) : 0;
	$sendback = add_query_arg( 'paged', $pagenum, $sendback );

	// Orderby
	if ( isset( $_REQUEST[ 'orderby' ] ) ) {
		$sOrderby = $_REQUEST[ 'orderby' ];
		$sendback = add_query_arg( 'orderby', $sOrderby, $sendback );
	}

	// Order
	if ( isset( $_REQUEST[ 'order' ] ) ) {
		$sOrder = $_REQUEST[ 'order' ];
		$sendback = add_query_arg( 'order', $sOrder, $sendback );
	}

	// Filters
	if ( isset( $_REQUEST[ 'mode' ] ) ) {
		$sMode = $_REQUEST[ 'mode' ];
		$sendback = add_query_arg( 'mode', $sMode, $sendback );
	}

	if ( isset( $_REQUEST[ 'mode' ] ) ) {
		$sMode = $_REQUEST[ 'mode' ];
		$sendback = add_query_arg( 'mode', $sMode, $sendback );
	}

	if ( isset( $_REQUEST[ 'm' ] ) ) {
		$sM = $_REQUEST[ 'm' ];
		$sendback = add_query_arg( 'm', $sM, $sendback );
	}

	if ( isset( $_REQUEST[ 's' ] ) ) {
		$sS = $_REQUEST[ 's' ];
		$sendback = add_query_arg( 's', $sS, $sendback );
	}

	if ( isset( $_REQUEST[ 'attachment-filter' ] ) ) {
		$sAttachmentFilter = $_REQUEST[ 'attachment-filter' ];
		$sendback = add_query_arg( 'attachment-filter', $sAttachmentFilter, $sendback );
	}

	if ( isset( $_REQUEST[ 'filter_action' ] ) ) {
		$sFilterAction = $_REQUEST[ 'filter_action' ];
		$sendback = add_query_arg( 'filter_action', $sFilterAction, $sendback );
	}

	// Get media taxonomy
	if ( isset( $_REQUEST[ 'media_category' ] ) ) {
		$sMediaTaxonomy = $_REQUEST[ 'media_category' ];
		$sendback = add_query_arg( 'media_category', $sMediaTaxonomy, $sendback );
	}

	return $sendback;
}

/**
 * Get an array of term values, which type is determined by the parameter
 *
 * @since 0.1.0
 */
function wp_media_categories_get_terms_values( $keys = 'ids' ) {

	// Get media taxonomy
	$media_terms = get_terms( 'media_category', array(
		'hide_empty' => 0,
		'fields'     => 'id=>slug',
	) );

	$media_values = array();
	foreach ( $media_terms as $key => $value ) {
		$media_values[] = ( $keys === 'ids' )
			? $key
			: $value;
	}

	return $media_values;
}

/**
 * Check the current action selected from the bulk actions dropdown.
 *
 * @since 0.1.0
 *
 * @return bool Whether 'bulk_toggle' was selected or not
 */
function wp_media_categories_is_action_bulk_toggle() {

	if ( isset( $_REQUEST[ 'action' ] ) && ( 'bulk_toggle' === $_REQUEST[ 'action' ] ) ) {
		return true;
	}

	if ( isset( $_REQUEST[ 'action2' ] ) && ( 'bulk_toggle' === $_REQUEST[ 'action2' ] ) ) {
		return true;
	}

	return false;
}

/**
 * For Media Category Management, the actual category should be used
 *
 * @since 0.1.0
 */
function wp_media_categories_custom_bulk_admin_footer() {
	global $post_type;

	// Make an array of post_type
	if ( is_array( $post_type ) ) {
		$wp_media_categories_post_type = $post_type;
	} else {
		$wp_media_categories_post_type   = array();
		$wp_media_categories_post_type[] = $post_type;
	}

	// Check whether the post_type array contains attachment
	if ( in_array( 'attachment', $wp_media_categories_post_type ) ) {

		// Get media taxonomy and corresponding terms
		$media_terms = get_terms( 'media_category', array(
			'hide_empty' => '0'
		) );

		// If terms found ok then generate the additional bulk_actions
		if ( ! empty( $media_terms ) && ! is_wp_error( $media_terms ) ) {

			// Create the box div string.
			$onChangeTxtTop = "jQuery(\'#bulk_tax_id\').val(jQuery(\'#bulk-action-selector-top option:selected\').attr(\'option_slug\'));";
			$onChangeTxtBottom = "jQuery(\'#bulk_tax_id\').val(jQuery(\'#bulk-action-selector-bottom option:selected\').attr(\'option_slug\'));";

			// Start the script to add bulk code
			$wp_media_categories_footer_script = "";
			$wp_media_categories_footer_script .= " <script type=\"text/javascript\">";
			$wp_media_categories_footer_script .= "jQuery(document).ready(function(){";

			// Add new hidden field to store the term_slug
			$wp_media_categories_footer_script .= "jQuery('#posts-filter').prepend('<input type=\"hidden\" id=\"bulk_tax_cat\" name=\"bulk_tax_cat\" value=\"" . 'media_category' . "\" />');";
			$wp_media_categories_footer_script .= "jQuery('#posts-filter').prepend('<input type=\"hidden\" id=\"bulk_tax_id\" name=\"bulk_tax_id\" value=\"\" />');";

			// Add new action to #bulk-action-selector-top
			$wp_media_categories_footer_script .= "jQuery('#bulk-action-selector-top')";
			$wp_media_categories_footer_script .= ".attr('onChange','" . $onChangeTxtTop . "')";
//				$wp_media_categories_footer_script .=	".attr('onClick','" . $onChangeTxt . "')";
			$wp_media_categories_footer_script .= ";";

			// Add new action to #bulk-action-selector-bottom
			$wp_media_categories_footer_script .= "jQuery('#bulk-action-selector-bottom')";
			$wp_media_categories_footer_script .= ".attr('onChange','" . $onChangeTxtBottom . "')";
//				$wp_media_categories_footer_script .=	".attr('onClick','" . $onChangeTxt . "')";
			$wp_media_categories_footer_script .= ";";

			// add bulk_actions for each category term
			foreach ( $media_terms as $term ) {
				$optionTxt = esc_js( __( 'Toggle', 'wp-media-categories' ) . ' ' . $term->name );
				$wp_media_categories_footer_script .= " jQuery('<option>').val('" . 'bulk_toggle' . "').attr('option_slug','" . $term->term_id . "').text('" . $optionTxt . "').appendTo(\"select[name='action']\");";
				$wp_media_categories_footer_script .= " jQuery('<option>').val('" . 'bulk_toggle' . "').attr('option_slug','" . $term->term_id . "').text('" . $optionTxt . "').appendTo(\"select[name='action2']\");";
			}

			$wp_media_categories_footer_script .= '});';
			$wp_media_categories_footer_script .= '</script>';

			echo $wp_media_categories_footer_script;
		}
	}
}

/**
 * Handle the custom Bulk Action
 *
 * @since 0.1.0
 */
function wp_media_categories_custom_bulk_action() {

	// Check parameters provided
	if ( ! isset( $_REQUEST[ 'bulk_tax_cat' ] ) ) {
		return;
	}

	if ( ! isset( $_REQUEST[ 'bulk_tax_id' ] ) ) {
		return;
	}

	if ( ! isset( $_REQUEST[ 'media' ] ) ) {
		return;
	}

	if ( ! wp_media_categories_is_action_bulk_toggle() ) {
		return;
	}

	// Set some variables
	$num_bulk_toggled       = 0;
	$media_taxonomy         = sanitize_key( $_REQUEST[ 'bulk_tax_cat' ] );
	$bulk_media_category_id = (int) $_REQUEST[ 'bulk_tax_id' ];

	// Process all media_id s found in the request
	foreach ( ( array ) $_REQUEST[ 'media' ] as $media_id ) {
		$media_id = ( int ) $media_id;

		// Check whether this user can edit this post
		if ( ! current_user_can( 'edit_post', $media_id ) ) {
			continue;
		}

		// Set so remove the $bulk_media_category taxonomy from this media post
		if ( has_term( $bulk_media_category_id, $media_taxonomy, $media_id ) ) {
			$bulk_result = wp_remove_object_terms( $media_id, $bulk_media_category_id, $media_taxonomy );

		// Not set so add the $bulk_media_category taxonomy to this media post
		} else {
			$bulk_result = wp_set_object_terms( $media_id, $bulk_media_category_id, $media_taxonomy, true );
		}

		if ( is_wp_error( $bulk_result ) ) {
			return $bulk_result;
		}

		// Keep track of the number toggled
		$num_bulk_toggled++;
	}

	// Create a sendback url to refresh the screen and report the results
	$sendback = wp_media_categories_create_sendback_url();
	$sendback = add_query_arg( array( 'bulk_toggled' => $num_bulk_toggled ), $sendback );

	wp_redirect( $sendback );
	exit();
}

/**
 * Display an admin notice on the Posts page after exporting
 *
 * @since 0.1.0
 */
function wp_media_categories_custom_bulk_admin_notices() {
	global $pagenow;

	if ( ( 'upload.php' === $pagenow ) && ! empty( $_REQUEST[ 'bulk_toggled' ] ) ) {
		$message = sprintf( _n( 'Media bulk toggled.', '%s media bulk toggled.', $_REQUEST[ 'bulk_toggled' ], 'wp-media-categories' ), number_format_i18n( $_REQUEST[ 'bulk_toggled' ] ) );
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	}
}

/**
 * Handle default category of attachments without category
 *
 * @since 0.1.0
 */
function wp_media_categories_set_attachment_category( $post_ID ) {

	// Check whether this user can edit this post
	if ( ! current_user_can( 'edit_post', $post_ID ) ) {
		return;
	}

	// Only add default if attachment doesn't have categories
	if ( ! wp_get_object_terms( $post_ID, 'media_category' ) ) {

		// Get the default value
		$default_category = get_option( 'default_media_category', 'uncategorized' );

		// Check for valid $default_category
		if ( 'uncategorized' !== $default_category ) {

			// Not set so add the $media_category taxonomy to this media post
			$add_result = wp_set_object_terms( $post_ID, $default_category, 'media_category', true );

			// Check for error
			if ( is_wp_error( $add_result ) ) {
				return $add_result;
			}
		}
	}
}
