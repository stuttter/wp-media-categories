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

		// Script
		wp_enqueue_script( 'wp-media-categories-media-views', $url . 'assets/js/media-views.js', array( 'media-views' ), $ver, true );

		$attachment_terms = json_decode( '[' . ltrim( $attachment_terms, ',' ) . ']', true );
		if ( ! is_array( $attachment_terms ) ) {
			$attachment_terms = array();
		}

		array_unshift(
			$attachment_terms,
			array(
				'term_id'   => 'no_category',
				'term_name' => __( 'No categories', 'wp-media-categories' ),
			)
		);

		$taxonomy_data = array(
			'media_category' => array(
				'list_title' => __( 'All categories', 'wp-media-categories' ),
				'term_list'  => $attachment_terms,
			),
		);

		wp_add_inline_script(
			'wp-media-categories-media-views',
			'var wp_media_categories_taxonomies = ' . wp_json_encode( $taxonomy_data ) . ';',
			'before'
		);
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
	$selected_value = isset( $_GET['term'] )
		? sanitize_key( wp_unslash( $_GET['term'] ) )
		: '';

	// Maybe looking for attachments with no terms
	if ( empty( $selected_value ) ) {
		$selected_value = isset( $_GET['media_category'] )
			? sanitize_key( wp_unslash( $_GET['media_category'] ) )
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
	$pagenum  = isset( $_REQUEST['paged'] ) ? absint( wp_unslash( $_REQUEST['paged'] ) ) : 0;
	$sendback = add_query_arg( 'paged', $pagenum, $sendback );

	// Orderby
	if ( isset( $_REQUEST['orderby'] ) ) {
		$sendback = add_query_arg( 'orderby', sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ), $sendback );
	}

	// Order
	if ( isset( $_REQUEST['order'] ) ) {
		$sendback = add_query_arg( 'order', sanitize_key( wp_unslash( $_REQUEST['order'] ) ), $sendback );
	}

	// Filters
	if ( isset( $_REQUEST['mode'] ) ) {
		$sendback = add_query_arg( 'mode', sanitize_key( wp_unslash( $_REQUEST['mode'] ) ), $sendback );
	}

	if ( isset( $_REQUEST['m'] ) ) {
		$sendback = add_query_arg( 'm', absint( wp_unslash( $_REQUEST['m'] ) ), $sendback );
	}

	if ( isset( $_REQUEST['s'] ) ) {
		$sendback = add_query_arg( 's', sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ), $sendback );
	}

	if ( isset( $_REQUEST['attachment-filter'] ) ) {
		$sendback = add_query_arg( 'attachment-filter', sanitize_key( wp_unslash( $_REQUEST['attachment-filter'] ) ), $sendback );
	}

	if ( isset( $_REQUEST['filter_action'] ) ) {
		$sendback = add_query_arg( 'filter_action', sanitize_text_field( wp_unslash( $_REQUEST['filter_action'] ) ), $sendback );
	}

	// Get media taxonomy
	if ( isset( $_REQUEST['media_category'] ) ) {
		$sendback = add_query_arg( 'media_category', sanitize_key( wp_unslash( $_REQUEST['media_category'] ) ), $sendback );
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
	$media_terms = get_terms( array(
		'taxonomy'   => 'media_category',
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

	if ( isset( $_REQUEST['action'] ) && ( 'bulk_toggle' === sanitize_key( wp_unslash( $_REQUEST['action'] ) ) ) ) {
		return true;
	}

	if ( isset( $_REQUEST['action2'] ) && ( 'bulk_toggle' === sanitize_key( wp_unslash( $_REQUEST['action2'] ) ) ) ) {
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
		$media_terms = get_terms( array(
			'taxonomy'   => 'media_category',
			'hide_empty' => false,
		) );

		// If terms found ok then generate the additional bulk_actions
		if ( ! empty( $media_terms ) && ! is_wp_error( $media_terms ) ) {

			$bulk_actions = array();
			foreach ( $media_terms as $term ) {
				$bulk_actions[ (string) absint( $term->term_id ) ] = __( 'Toggle', 'wp-media-categories' ) . ' ' . $term->name;
			}

			$script = '(function($){$(function(){'
				. "\$('#posts-filter').prepend(\$('<input>',{type:'hidden',id:'bulk_tax_cat',name:'bulk_tax_cat',value:'media_category'}));"
				. "\$('#posts-filter').prepend(\$('<input>',{type:'hidden',id:'bulk_tax_id',name:'bulk_tax_id',value:''}));"
				. "\$('#bulk-action-selector-top').on('change',function(){\$('#bulk_tax_id').val(\$(this).find('option:selected').attr('option_slug'));});"
				. "\$('#bulk-action-selector-bottom').on('change',function(){\$('#bulk_tax_id').val(\$(this).find('option:selected').attr('option_slug'));});"
				. '$.each(' . wp_json_encode( $bulk_actions ) . ",function(termId,label){\$('<option>',{value:'bulk_toggle',text:label}).attr('option_slug',termId).appendTo(\"select[name='action'],select[name='action2']\");});"
				. '});})(jQuery);';

			wp_add_inline_script( 'jquery-core', $script );
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

	// Verify the nonce generated by the Media Library bulk-action form.
	check_admin_referer( 'bulk-media' );

	// Set some variables
	$num_bulk_toggled       = 0;
	$media_taxonomy         = sanitize_key( wp_unslash( $_REQUEST['bulk_tax_cat'] ) );
	$bulk_media_category_id = absint( wp_unslash( $_REQUEST['bulk_tax_id'] ) );

	// Process all media_id s found in the request
	$media_ids = array_map( 'absint', wp_unslash( (array) $_REQUEST['media'] ) );
	foreach ( $media_ids as $media_id ) {

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

	wp_safe_redirect( $sendback );
	exit();
}

/**
 * Display an admin notice on the Posts page after exporting
 *
 * @since 0.1.0
 */
function wp_media_categories_custom_bulk_admin_notices() {
	global $pagenow;

	if ( ( 'upload.php' === $pagenow ) && ! empty( $_REQUEST['bulk_toggled'] ) ) {
		$num_bulk_toggled = absint( wp_unslash( $_REQUEST['bulk_toggled'] ) );
		/* translators: %s: Number of media attachments updated. */
		$message = sprintf( _n( '%s media attachment bulk toggled.', '%s media attachments bulk toggled.', $num_bulk_toggled, 'wp-media-categories' ), number_format_i18n( $num_bulk_toggled ) );
		echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
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
