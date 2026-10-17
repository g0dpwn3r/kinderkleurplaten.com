<?php
/*
 * widget.php
 * Gwolle-GB Widget Average Rating
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( function_exists('register_sidebar') && class_exists('WP_Widget') ) {

	class GwolleGB_Widget_Av_Rating_v2 extends WP_Widget {

		/* Constructor */
		public function __construct() {
			$widget_ops = array(
				'classname' => 'gwolle_gb_av_rating',
				'description' => esc_html__( 'Displays the average star rating of a guestbook.', 'gwolle-gb' ),
				);
			parent::__construct( 'gwolle_gb_av_rating', esc_html__('Gwolle GB: Average Star Rating', 'gwolle-gb'), $widget_ops );
			$this->alt_option_name = 'gwolle_gb_av_rating';
		}

		/** @see WP_Widget::widget */
		public function widget( $args, $instance ) {

			$default_value = array(
					'title'    => esc_html__('Average Star Rating', 'gwolle-gb'),
					'book_id'  => 1,
				);
			$instance      = wp_parse_args( (array) $instance, $default_value );

			$widget_title  = esc_attr($instance['title']);
			$book_id       = (int) esc_attr($instance['book_id']);

			$widget_html = '';

			$widget_html .= $args['before_widget'];
			$widget_html .= '
				<div class="gwolle-gb-widget-av-rating">';

			if ($widget_title !== FALSE) {
				$widget_html .= $args['before_title'] . apply_filters('widget_title', $widget_title) . $args['after_title'];
			}

			$query_args = array(
					'checked'  => 'checked',
					'trash'    => 'notrash',
					'spam'     => 'nospam',
					'book_id'  => $book_id,
				);
			$widget_html .= gwolle_gb_addon_starrating_average_html_v2( '', $query_args, $widget_title );

			$widget_html .= '
				</div>
				' . $args['after_widget'];

			echo $widget_html;

			// Make sure to load it with the widget, also on non-guestbook pages and even when it is disabled.
			gwolle_gb_enqueue_starrating();

		}

		/** @see WP_Widget::update */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title']   = wp_strip_all_tags( $new_instance['title'] );
			$instance['book_id'] = (int) $new_instance['book_id'];
			return $instance;
		}

		/** @see WP_Widget::form */
		public function form( $instance ) {

			$default_value = array(
					'title'       => esc_html__('Average Star Rating', 'gwolle-gb'),
					'book_id'     => 1,
				);
			$instance = wp_parse_args( (array) $instance, $default_value );

			$title    = esc_attr($instance['title']);
			$book_id  = (int) esc_attr($instance['book_id']);
			?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>" /><?php esc_html_e('Title:', 'gwolle-gb'); ?></label>
				<br />
				<input type="text" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" value="<?php echo esc_attr( $title ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" />
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('book_id') ); ?>" /><?php esc_html_e('Book ID:', 'gwolle-gb'); ?></label>
				<br />
				<input type="text" id="<?php echo esc_attr( $this->get_field_id('book_id') ); ?>" value="<?php echo (int) $book_id; ?>" name="<?php echo esc_attr( $this->get_field_name('book_id') ); ?>" /><br />
			</p>
			<?php
		}

	}

	function gwolle_gb_addon_widget_average_rating_v2() {
		register_widget( 'GwolleGB_Widget_Av_Rating_v2' );
	}
	add_action( 'widgets_init', 'gwolle_gb_addon_widget_average_rating_v2' );

}
