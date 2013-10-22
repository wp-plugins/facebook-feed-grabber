<?php 
/**
 * HTML Template for the verify app credentials button.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */

?>
<script type="text/template" id="appNameTemplate">
	<img alt="" class="icon" src="<%= icon %>" /> <strong>Name:</strong> <%= name %>
</script>
<input type="button" name="ffg_verify" value="<?php echo $value; ?>" class="button" id="ffg-verify" />
<span id="ffg_verify_d" class="description"></span>
<script>
	// window.ffgOptions is defined via WP's wp_localize_script.
	var ffg = window.ffgOptions;
	ffg.panels.push({
		json : <?php echo $appMeta; ?>,
		key : 'verifyApp',
		templateID : '#appNameTemplate',
		tagID : '#ffg_verify_d',
		className : 'description'
	});
</script>