<?php 
/**
 * HTML Template for the select field.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */
?>
<select name="ffg_options[<?php echo $key; ?>]">
	<?php
	foreach ($options as $value => $name) {
		if ( $selected == $value )
			$select = " selected='selected'";
		else
			$select = null;
		
		echo "<option value='". $value ."'$select>". esc_attr($name) ."</option>\n";
	}
	?>
</select>
<span class="description"><?php echo $descript; ?></span>
<?php  if ( $error != null ) : ?>
 <span class="error-message"><?php echo $error; ?></span>
<?php endif; ?>
