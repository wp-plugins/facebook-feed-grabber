<?php

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class ffg_Widget extends WP_Widget {
	
	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function ffg_Widget() {
		$widget_ops = array( 'classname' => 'ffg_widget', 'description' => 'Displays the specified feed. If no feed is specified the it defaults to the default feed of Facebook Feed Grabber.' );
		$this->WP_Widget( 'ffg_widget', 'Facebook Feed Grabber', $widget_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array  An array of standard parameters for widgets in this theme 
	 * @param array  An array of settings for this widget instance 
	 * @return void Echoes it's output
	 **/
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		
		$feed = ( $instance['feed'] != null ) ? $instance['feed'] : null;
		
		echo $before_widget;

		fb_feed($feed, $args);
		
		echo $after_widget;
	}

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings 
     * @return array The validated and (if necessary) amended settings
     **/
	function update( $new_instance, $old_instance ) {
		$updated_instance['feed'] = ( ctype_digit($new_instance['feed']) !== false ) ? $new_instance['feed'] : null;
		
		return $updated_instance;
	}

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'feed' => null ) );
		extract($instance);
		?>
		
		<p>If you wish to display the default feed then leave blank. Else fill in with the page id or username.</p>
		<?php /*if ( $page != null && $location != null ): ?>
			<div class="warning" style="padding-top: 4px;"><p>Please fill in one field only.</p></div>
		<?php endif;*/ ?>
		<p>
		<label for="<?php echo $this->get_field_id('feed'); ?>"><?php echo 'Feed ID:'; ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('feed'); ?>" name="<?php echo $this->get_field_name('feed'); ?>" type="text" value="<?php echo esc_attr($feed); ?>" />
		</p>
		
		<?php
	}
}

add_action( 'widgets_init', create_function( '', "register_widget('ffg_Widget');" ) );

?>