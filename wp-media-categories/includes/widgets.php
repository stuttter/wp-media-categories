<?php

/**
 * Media Categories Widgets
 *
 * @package Media/Categories/Widgets
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Categories widget class
 *
 * @since 0.1.0
 */
class WP_Media_Categories extends WP_Widget {

	public function __construct() {
		parent::__construct( 'wp_media_categories_categories', __( 'Categories', 'wp-media-categories' ), array(
			'classname'   => 'wp_media_categories_widget_categories',
			'description' => __( 'A list or dropdown of categories.', 'wp-media-categories' )
		) );
	}

	public function widget( $args, $instance ) {

		$title = empty( $instance['title'] )
			? __( 'Categories', 'wp-media-categories' )
			: $instance['title'];

		// This filter is documented in wp-includes/default-widgets.php
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$c = ! empty( $instance['count'] )        ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] )     ? '1' : '0';

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$media_cat_args = wp_media_categories_get_media_category_options();
		$media_cat_args['show_count']   = $c;
		$media_cat_args['hierarchical'] = $h;
		$media_cat_args = array(
			'taxonomy'     => 'media_category',
			'orderby'      => 'name',
			'show_count'   => $c,
			'hierarchical' => $h,
		);

		if ( ! empty( $d ) ) :
			static $first_dropdown = true;

			$dropdown_id = ( true === $first_dropdown )
				? 'media_category'
				: "{$this->id_base}-dropdown-{$this->number}";

			$first_dropdown = false;

			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $title . '</label>';

			$media_cat_args['show_option_none'] = __( 'Select Category', 'wp-media-categories' );
			$media_cat_args['id']               = $dropdown_id;
			$media_cat_args['value_field']      = 'slug';

			// Get the taxonomy slug
			$slug = get_taxonomy( $media_cat_args['taxonomy'] )->rewrite['slug'];

			/**
			 * Filter the arguments for the Categories widget drop-down.
			 *
			 * @since 1.6.0
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param array $media_cat_args An array of Categories widget drop-down arguments.
			 */
			wp_dropdown_categories( apply_filters( 'wp_media_categories_widget_categories_dropdown_args', $media_cat_args ) ); ?>

			<script type='text/javascript'>
			/* <![CDATA[ */
			(function() {
				var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
				function onMediaCatChange() {
					if ( dropdown.options[ dropdown.selectedIndex ].value !== -1 ) {
						location.href = "<?php echo home_url( $slug ); ?>/" + dropdown.options[ dropdown.selectedIndex ].value;
					}
				}
				dropdown.onchange = onMediaCatChange;
			})();
			/* ]]> */
			</script>

		<?php else : ?>

			<ul>

				<?php

				$media_cat_args['title_li'] = '';

				/**
				 * Filter the arguments for the Media Categories widget.
				 *
				 * @since 1.6.0
				 *
				 * @param array $media_cat_args An array of Media Categories widget options.
				 */
				wp_list_categories( apply_filters( 'wp_media_categories_widget_categories_args', $media_cat_args ) ); ?>

			</ul>

		<?php

		endif;

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags($new_instance['title']);
		$instance['count']        = !empty($new_instance['count'])        ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown']     = !empty($new_instance['dropdown'])     ? 1 : 0;

		return $instance;
	}

	public function form( $instance ) {
		$instance     = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title        = esc_attr( $instance['title'] );
		$count        = isset($instance['count'])          ? (bool) $instance['count']        : false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown     = isset( $instance['dropdown'] )     ? (bool) $instance['dropdown']     : false; ?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'wp-media-categories' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
			<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown', 'wp-media-categories' ); ?></label>
			<br />

			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts', 'wp-media-categories' ); ?></label>
			<br />

			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
			<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy', 'wp-media-categories' ); ?></label>
		</p>

	<?php
	}
}

/**
 * Register all of the WP widgets on startup.
 *
 * Calls 'widgets_init' action after all of the widgets have been registered.
 *
 * @since 1.6.0
 */
function wp_media_categories_register_widgets() {
	register_widget( 'WP_Media_Categories' );
}
