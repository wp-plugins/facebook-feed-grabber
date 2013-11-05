<?php

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class ffg_Widget extends WP_Widget {
	
	protected $instance = array();
	
	static $defaults = array(
		'feed' => 'default_feed',
		'num_entries' => 'num_entries',
		'show_title' => 'show_title',
		'limit' => 'limit',
		'show_thumbnails' => 'show_thumbnails',
	);
	
	
	/**
	 * Get the default widget settings based on the current plugin settings.
	 *
	 * @return array
	 **/
	public static function get_defaults(  ) {
		global $ffg_setup;
		
		$options = $ffg_setup->get_options('ffg_options');
		
		foreach( self::$defaults as $key => $value) {
			$defaults[$key] = $options[$value];
		}
		
		return $defaults;
	} // End function get_defaults
	
	
	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function ffg_Widget() {
		
		$this->get_defaults();
		
		$widget_ops = array(
			'classname' => 'ffg_widget',
			'description' => 'Display a Facebook feed.'
		);
		
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
		
		$feed = ( isset($instance['feed']) ) ? $instance['feed'] : null;
		
		echo $before_widget;

		fb_feed($feed, $instance);
		
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
		$updated_instance['num_entries'] = intval($new_instance['num_entries']);
		$updated_instance['show_title'] = ( isset($new_instance['show_title']) ) ? 1 : 0;
		$updated_instance['limit'] = ( isset($new_instance['limit']) ) ? 1 : 0;
		$updated_instance['show_thumbnails'] = ( isset($new_instance['show_thumbnails']) ) ? 1 : 0;
		
		$defaults = $this->get_defaults();
		
		foreach ( $updated_instance as $key => $value ) {
			if ( $value == $defaults[$key] )
				unset($updated_instance[$key]);
		}
		
		return $updated_instance;
	}
	
	
    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array | An array of the current settings for this widget
     * @return void | Echoes it's output
     **/
	function form( $instance ) {
		$this->instance = $instance;
		
		extract(wp_parse_args( (array) $instance, self::get_defaults() ));
		?>
		
		<p>Displays a feed using the default settings. You may override the defaults below.</p>
				
		<?php
			$this->form_field('feed', 'Feed ID', $feed);
			
			$this->form_field('num_entries', 'Number of Entries', $num_entries);
			
			$this->form_field('show_title', 'Show Title', $show_title, true);
			
			$this->form_field('limit', 'Limit to Posts From Feed', $limit, true);
			
			$this->form_field('show_thumbnails', 'Show Thumbnails', $show_thumbnails, true);
		?>
		<script type="text/javascript" charset="utf-8">
		/* <![CDATA[ */
			jQuery(document).ready(function() {
				ffg_setOnClick();
			});
		/* ]]> */
		</script>
		<?php
	}
	
	/**
	 * Creates a field for the widget options.
	 *
	 * @param string
	 * @param string
	 * @return void | Echoes it's output.
	 **/
	function form_field( $id, $name, $value, $bool = false ) {
		$defaults = self::get_defaults();
		
		$displayField = ( $defaults[$id] == $value ) ? ' style="display:none;"' : null ;
		$displayEdit = ( $defaults[$id] != $value ) ? ' style="display:none;"' : null ;
		?>
		<p>
			<label for="<?php echo $this->get_field_id($id); ?>"><?php echo $name; ?>:</label> <span<?php echo $displayEdit; ?>><span class="description"><?php echo ( $bool ) ? $this->yesorno($value) : esc_attr($value); ?></span> (<a href="#<?php echo $id; ?>" class="fbfeed-editoption">Edit</a>)</span>
			<?php
			if ( $bool ) {
				?>
				<input class="option" id="<?php echo $this->get_field_id($id); ?>" name="<?php echo $this->get_field_name($id); ?>" type="checkbox" value="1"<?php echo $displayField; echo ( $value ) ? ' checked="checked"' : null; ?> />
				<?php
			} else {
				?>
				<input class="widefat option" id="<?php echo $this->get_field_id($id); ?>" name="<?php echo $this->get_field_name($id); ?>" type="text" value="<?php echo esc_attr($value); ?>"<?php echo $displayField; ?> />
				<?php
			}
			?>
		</p>
		<?php
	} // End function form_field
	
	
	/**
	 * Takes a boolean value and returns "Yes" or "No" as appropriate.
	 *
	 * @param Boolean
	 * @return string
	 **/
	function yesorno( $value ) {
		if ( $value )
			return 'Yes';
		else
			return 'No';
	} // End function yesorno

	
	/**
	 * Queue's our javascript for the widget admin page.
	 *
	 * @param string
	 * @return void
	 */
	public static function javascript( $hook ) {
	    if( 'widgets.php' != $hook )
	        return;
	
	    wp_enqueue_script( 'ffg-widget-admin', plugins_url('/js/ffg-widget-options.js', __FILE__) );
		
		// We need to feed some stuff to our script
		// This allows us to pass PHP variables to the Javascript code. We can pass multiple vars in the array.
		wp_localize_script( 'ffg-widget-admin', 'ffg_widget_admin', array(
			'defaults' => self::get_defaults(),
		));
	
	}
	
} // End class ffg_Widget

// Hook in the widget.
add_action( 'widgets_init', create_function( '', "register_widget('ffg_Widget');" ) );

// Hook in our javascript for the widgets admin page.
add_action( 'admin_enqueue_scripts', array('ffg_Widget', 'javascript') );

?>