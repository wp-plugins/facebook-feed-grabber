<?php 
/**
 * HTML Template for an admin checkbox input and description.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */

$checked =  $value ? ' checked="checked" ' : null;
?>
<fieldset>
	<legend class="screen-reader-text"><span><?php echo $descript; ?></span></legend>
	<label for="ffg-<?php echo $key; ?>"><input <?php echo $checked; ?> type="checkbox" value="1" id="ffg-<?php echo $key; ?>" name="ffg_options[<?php echo $key; ?>]"> <?php echo $descript; ?></label>
</fieldset>
