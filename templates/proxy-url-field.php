<?php 
/**
 * HTML Template for the proxy URL field.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */

$hide = ' style="display: none;"';

?>
<div id="proxyDisabled"<?php echo ! empty($value) ? $hide : null; ?>>
	<a href="#enableProxy"><?php _e('Enable Proxy'); ?></a> - <span class="description"><?php _e('Click to enable if you\'re server is behind a proxy.') ?></span>
</div>
<div id="proxyEnabled"<?php echo empty($value) ? $hide : null; ?>>
	<input type="text" name="ffg_options[proxy_url]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
	<span class="description"><?php echo $descript; ?></span>
</div>
