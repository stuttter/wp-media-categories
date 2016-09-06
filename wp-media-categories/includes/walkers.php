<?php

/**
 * Media Categories Walkers
 *
 * @package Media/Categories/Walkers
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Custom walker for wp_dropdown_categories
 *
 * @since 0.1.0
 */
class WP_Media_Categories_Filter_Walker extends Walker_CategoryDropdown {

	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		$pad = str_repeat( '&nbsp;', $depth * 3 );
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		if ( ! isset( $args['value'] ) ) {
			$args['value'] = 'slug';
		}

		$value = ( 'slug' === $args['value'] )
			? $category->slug
			: $category->term_id;

		$output .= '<option class="level-' . $depth . '" value="' . $value . '"';
		if ( $value === (string) $args['selected'] ) {
			$output .= ' selected="selected"';
		}
		$output .= '>';
		$output .= $pad . $cat_name;

		if ( ! empty( $args['show_count'] ) ) {
			$output .= '&nbsp;(' . $category->count . ')';
		}

		$output .= "</option>\n";
	}
}

/**
 * Custom walker for wp_dropdown_categories for media grid view filter
 *
 * @since 0.1.0
 */
class WP_Media_Categories_Media_Grid_Walker extends Walker_CategoryDropdown {

	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad      = str_repeat( '&nbsp;', $depth * 3 );
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		$output .= ',{"term_id":"' . $category->term_id . '",';

		$output .= '"term_name":"' . $pad . esc_attr( $cat_name );
		if ( $args['show_count'] ) {
			$output .= '&nbsp;&nbsp;('. $category->count .')';
		}

		$output .= '"}';
	}
}

/**
 * Based on /wp-includes/category-template.php
 *
 * @since 0.1.0
 */
class WP_Media_Categories_Checklist_Walker extends Walker {
	var $tree_type = 'category';
	var $db_fields = array (
		'parent' => 'parent',
		'id'     => 'term_id'
	);

	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		// Get taxonomy
		$taxonomy = empty( $args['taxonomy'] )
			? 'media_category'
			: $args['taxonomy'];

		$name = 'tax_input[' . $taxonomy . ']';

		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'>";
		$output .= '<label class="selectit">';
		$output .= '<input value="' . $category->slug . '" ';
		$output .= 'type="checkbox" ';
		$output .= 'name="'.$name.'['. $category->slug.']" ';
		$output .= 'id="in-'.$taxonomy.'-' . $category->term_id . '"';
		$output .= checked( in_array( $category->term_id, $args['selected_cats'] ), true, false );
		$output .= disabled( empty( $args['disabled'] ), false, false );
		$output .= ' /> ';
		$output .= esc_html( apply_filters( 'the_category', $category->name ) );
		$output .= '</label>';
	}

	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}
