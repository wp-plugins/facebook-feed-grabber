<?php 
/**
 * HTML Template for the default feed field.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */
?>
<script type="text/template" id="feedNameTemplate">
	<strong><%= feedType %>:</strong> <%= name %>
</script>
<div id="default-feed-container" class="field-container">
	<input type="text" name="ffg_options[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" /> <br />
	<div id="default-feed-name" class="fieldMeta"></div>
</div>
<span class="description"><?php echo $descript; ?></span>
<script>
	// window.ffgOptions is defined via WP's wp_localize_script.
	var ffg = window.ffgOptions;
	ffg.panels.push({
		json : <?php echo $feedMeta; ?>,
		key : 'defaultFeed',
		templateID : '#feedNameTemplate',
		tagID : '#default-feed-name',
		className : 'feedName'
	});
</script>