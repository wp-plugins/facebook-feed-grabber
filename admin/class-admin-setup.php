<?php 
/**
 * Sets up the admin stuff.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */


/**
 * Admin Setup
 * 
 * Admin Setup
 * 
 * @since 0.9.0
 * 
 * @return void 
 */
class ffg_admin_setup
{
	
	/**
	 * Admin file Prefix
	 * 
	 * @var string Admin file prefix.
	 */
	public $file_prefix = 'class-admin';

	/**
	 * Option panels to be added.
	 * 
	 * @var array An array of panels.
	 */
	public $options_panels = array(
		'options' => 'Facebook Feed Grabber Options',
		);

	function __construct()
	{
		add_action('init', array($this, 'init'));
	}

	/**
	 * Initiate the admin stuff.
	 * 
	 * Hooks in our options panels.
	 * 
	 * @since 0.9.0
	 * 
	 * @return void 
	 */
	public function init(  )
	{
		$this->options_panels();
	}


	/**
	 * Hook in our options panels.
	 */
	public function options_panels(  )
	{
		include_once $this->file_prefix .'-base.php';

		foreach ( $this->options_panels as $key => $name ) {
			include_once $this->file_prefix .'-'. $key .'.php';
		}
	}

}

new ffg_admin_setup;
?>