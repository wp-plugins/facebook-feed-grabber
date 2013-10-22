<?php 
/**
 * HTML Template for an admin text input and description.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */

$autocomplete = ( ! $autocomplete ) ? 'autocomplete="off" ' : null;
?>
<input type="text" name="ffg_options[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" id="ffg-<?php echo $key; ?>" <?php echo $autocomplete ?>/>
<span class="description"><?php echo $descript; ?></span>
