<?php 
/**
 * HTML Template for the proxy URL field.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */

?>
<fieldset>
	<legend class="screen-reader-text"><span>Date Format</span></legend>

	<label><input type="radio"<?php echo ( $value == 'style.css' ) ? 'checked="checked"' : null; ?> value="style.css" name="ffg_options[<?php echo $key ?>]"> <span><?php _e('Use Default Style Sheet'); ?></span> <span class="description"><?php _e('Font color and size will be based on the theme\'s style rules for the current context.') ?></span></label><br />

	<label><input type="radio"<?php echo ( $value == 'style-2.css' ) ? 'checked="checked"' : null; ?> value="style-2.css" name="ffg_options[$key]"> <span><?php _e('Use Secondary Style Sheet'); ?></span> <span class="description"><?php _e('This one is more specific in it\'s declarations than the default. It requires the feed container to have an id of "fb-feed".') ?></span></label><br />

	<label><input type="radio"<?php echo ( $value == false ) ? 'checked="checked"' : null; ?> value="0" name="ffg_options[<?php echo $key ?>]"> <span><?php _e("I'll Define My Own Styles."); ?></span></label><br />
</fieldset>
