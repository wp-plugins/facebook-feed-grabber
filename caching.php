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
	 * @access public
	 * @var string The folder to store the cache in.
	 */
	public $folder = null;
	
	/**
	 * Prefix for file names.
	 * 
	 * @since 0.7
	 * @access public
	 * @var string Prefix for the cache's file names.
	 */
	public $prefix = 'ffg-';
	
	/**
	 * Instantiate the class.
	 * 
	 * Get's the plugin's options and stores the folder.
	 * 
	 * @since 0.7
	 * @return void Doesn't return anything special.
	 */
	function __construct(  ) {

		/*
			LBTD: Make this use the new options retrieval method.
		*/
		
		// Get stored plugin options
		$options = get_option('ffg_options');
		
		// Set the cache folder
		$this->folder = $options['cache_folder'];
		
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
	function theMagic( $fb, $file, $expires ) {
			
		// Check if it has already been cached and not expired
		// If true then we output the cached file contents and finish
		if ( $this->isCached( $file, $expires ) )
			return json_decode($this->getCache($file), true);
			
		$content = $fb->facebook->api($file);
		
		// Save it to the cache for next time
		$this->saveCache($file, json_encode($content));
		
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
	function getName( $file ) {
		return $this->prefix . md5($file) . ".json";
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
	function isCached( $file, $expires ) {		
		
		// Get file path/name.
		$cache_file = $this->folder . $this->getName($file);
		
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
	function getCache( $file ) {
		$cache_file = $this->folder . $this->getName($file);
		
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
	function saveCache($file, $content) {
		
		$cache_file = $this->folder . $this->getName($file);
		
		// See if there is a cache folder and that it's writable. 
		if ( wp_mkdir_p($this->folder) && ( ! file_exists($cache_file) || is_writable($cache_file) ) ) {
		
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