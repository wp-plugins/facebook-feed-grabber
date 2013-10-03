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
	 * Get the plugin cache folder.
	 * 
	 * Get the plugin cache folder with is hidden away in the
	 * plugin preferences. 
	 * 
	 * @since 0.9.0
	 * 
	 * @return string The cache folder.
	 */
	public static function cache_folder() {

		// If we already have the cache folder then return it.
		if ( self::$folder != null )
			return self::$folder;

		// Get stored plugin options
		$options = 	ffg_base::get_options();

		// Set the cache folder
		self::$folder = $options['cache_folder'];

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
	 * @return boolean If file is cached. 
	 */
	public static function isCached( $file, $expires ) {		
		
		// Get file path/name.
		$cache_file = self::cache_folder() . self::getName($file);
		
		// When was the file created.
		$cachefile_created = ( file_exists($cache_file) ) ? @filemtime($cache_file) : 0;
					
		// Return if it's valid or not.
		return ( (time() - $expires) < $cachefile_created );
	}
	
	
	/**
	 * Get a file
	 * 
	 * Get the cached file.
	 * 
	 * @since 0.7
	 * 
	 * @param string $file The base file name used for the cached file.
	 * 
	 * @return string The contents of the cached file.
	 */
	public static function getCache( $file ) {
		$cache_file = self::cache_folder() . self::getName($file);
		
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
		
		$cache_file = self::cache_folder() . self::getName($file);
		
		// See if there is a cache folder and that it's writable. 
		if ( wp_mkdir_p(self::cache_folder()) && ( ! file_exists($cache_file) || is_writable($cache_file) ) ) {
		
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