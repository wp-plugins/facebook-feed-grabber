<?php 
/**
 * Admin options base.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */

/**
 * The base admin class.
 * 
 * The base admin class, extends ffg_base.
 * 
 * @since 0.9.0
 * 
 * @return void 
 */
class ffg_admin_base extends ffg_base 
{

	/**
	 * for storing our options page hook suffix.
	 * 
	 * @since 0.9.0
	 * @var string Page hook suffix.
	 */
	public $page = null;

	/**
	 * Value to use for action attribute of form.
	 * 
	 * @var string Action attribute.
	 */
	public $action = 'options.php';
	
	/**
	 * Gets this class ready.
	 * 
	 * Get's the plugin options.
	 * 
	 * @return void 
	 */
	function __construct(  ) {

		$this->options = ffg_base::get_options('ffg_options');

	}
	// End __construct()
	
		
	/**
	 * The options page.
	 * 
	 * The options page.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	public function options_page() {
		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		?>
	    <div id="fb-root"></div>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php _e($this->menu_title) ?></h2>
			<form action="<?php echo $this->action; ?>" method="post">
			<?php settings_fields($this->option_group); ?>
			<?php do_settings_sections($this->page); ?>
			<p class="submit">
				<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
			</p>
			</form>
		</div>
		<?php
	}
	
} // End class ffg_admin
?>