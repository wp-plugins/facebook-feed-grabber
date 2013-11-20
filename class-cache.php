<?php

/**
 * Class for caching/retrieving requests.
 * 
 * Contains methods and properties for caching the results from
 * Facbook.
 * 
 * @since 0.7
 */
class ffg_cache {
	
	/**
	 * Folder to cache files to.
	 * 
	 * @since 0.7
	 * @access private
	 * @var string The folder to store the cache in.
	 */
	private static $folder = null;
	
	/**
	 * Prefix for file names.
	 * 
	 * @since 0.7
	 * @access public
	 * @var string Prefix for the cache's file names.
	 */
	public static $prefix = 'ffg-';
	
	/**
	 * Doesn't do anything as of 0.9. 
	 * 
	 * @since 0.7
	 * @return void
	 */
	public function __construct(  ) {
	}


	/**
	 * Get the cache folder base.
	 * 
	 * Get the cache folder base which is stored away in the
	 * plugin options making it possible to change it. If the 
	 * value of the stored option is 'wp-content' then we 
	 * will get the wp-content directory stored in 
	 * WP_CONTENT_DIR. This makes the process of moving from 
	 * a dev to production environment transparent when the 
	 * option has not been touched.
	 * 
	 * @since 0.9.0
	 * @return string The cache folder base.
	 */
	public static function cache_base()
	{

		static $folder = null;

		if ( is_string($folder) )
			return $folder;

		// Get stored plugin options
		$options = 	ffg_base::get_options();

		// Set the cache folder
		$folder = $options['cache_base'];

		if ( $folder == 'wp-content' )
			$folder = WP_CONTENT_DIR;

		return $folder;
	}

	/**
	 * Get the cache folder which is appended to cache base.
	 * 
	 * Get the plugin cache folder which is hidden away in the
	 * plugin options making it possible to change it. Returns false if the directory is not writable. 
	 * 
	 * @since 0.9.0
	 * 
	 * @return mixed The cache folder or false if it's not writable.
	 */
	public static function cache_folder() {

		// If we've already set the cache folder then return it.
		if ( is_string(self::$folder) )
			return self::$folder;

		// If we've declared the cache folder unwritable.
		if ( self::$folder === false )
			return false;

		// Get stored plugin options
		$options = 	ffg_base::get_options();

		// Set the cache folder
		$folder = self::cache_base() . $options['cache_folder'];

		// See if the cache folder is writable. 
		if ( ! wp_mkdir_p($folder) )
			self::$folder = false;
		else
			self::$folder = $folder;

		return self::$folder;
	}
	
	/**
	 * Does the whole process.
	 * 
	 * Checks to see if the requested conent is cached,
	 * gets the content whether cached or not.
	 * saves new content to the cache.
	 * 
	 * @since 0.7
	 * 
	 * @param object $fb A Facebook class object.
	 * @param string $file The Facebook content to get.
	 * @param string $expires Cache expiration as a unix timestamp.
	 * 
	 * @return string The content.
	 */
	public static function theMagic( $fb, $file, $expires ) {
			
		// Check if it has already been cached and not expired
		// If true then we output the cached file contents and finish
		if ( self::isCached( $file, $expires ) )
			return json_decode(self::getCache($file), true);
			
		$content = $fb->facebook->api($file);
		
		// Save it to the cache for next time
		self::saveCache($file, json_encode($content));
		
		return $content;
	}
	
	/**
	 * Get the file name.
	 * 
	 * Generates and returns the file name to use 
	 * for the cached content.
	 * 
	 * @since 0.7
	 * 
	 * @param string $file A base name for the cached file.
	 * 
	 * @return string The file name to be used for caching.
	 */
	public static function getName( $file ) {
		return self::$prefix . md5($file) . ".json";
	}
	
	
	/**
	 * Check to see if a file is cached
	 * 
	 * Checks to see if a file has been cached. 
	 * Returns true if it's found and false if it's not.
	 * 
	 * @since 0.7
	 * 
	 * @param string $file The base file name used for the cached file.
	 * @param string $expires Cache expiration as a unix timestamp.
	 * 
	 * @return boolean Is the file cached and valid?
	 */
	public static function isCached( $file, $expires ) {

		if ( ! self::cache_folder() )
			return false;

		// Get file path/name.
		$cache_file = self::$folder . self::getName($file);
		
		// When was the file created.
		$cachefile_created = ( file_exists($cache_file) ) ? @filemtime($cache_file) : 0;
					
		// Return if it's valid or not.
		return ( (time() - $expires) < $cachefile_created );
	}
	
	
	/**
	 * Get a file
	 * 
	 * Get the cached file. This function does no validation
	 * beyond verifying the cache folder is valid, it then
	 * returns the results of file_get_contents.
	 * 
	 * @since 0.7
	 * 
	 * @param string $file The base file name used for the cached file.
	 * 
	 * @return string The contents of the cached file.
	 */
	public static function getCache( $file ) {

		if ( ! self::cache_folder() )
			return false;

		$cache_file = self::$folder . self::getName($file);
		
		return file_get_contents($cache_file);
	}
	
	
	/**
	 * Caches a file
	 * 
	 * Caches the file if the cache directory is there and writable.
	 * If the directory is not there it tries to make it. 
	 * Returns TRUE on success and FALSE on failure.
	 * 
	 * @since 0.7
	 * 
	 * @param string $file The base file name used for the cached file.
	 * @param string $content The contents to cache.
	 * 
	 * @return boolean Results of it's endeavor. 
	 */
	public static function saveCache($file, $content) {

		// See if there's a cache folder that's writable.
		if ( ! self::cache_folder() )
			return false;
		
		// The file path and name to cache.
		$cache_file = self::$folder . self::getName($file);
		
		// Does the file exist, can we write to it?
		if ( ! file_exists($cache_file) || is_writable($cache_file) ) {
		
			$fp = fopen($cache_file, 'w');
			$write = fwrite($fp, $content);
			fclose($fp);
			
			if ( $write )
				return true;
			else
				return false;
			
		} else 
			return false;
		
	}
	
}// End class ffg_cache

?>