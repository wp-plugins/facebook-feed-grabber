<?php

/* - - - - - -
	
	Class for caching/retrieving requests.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
class ffg_cache {
	
	// Folder to cache files to.
	public $folder = null;
	
	// Prefix for file names.
	public $prefix = 'ffg-';
	
	function __construct(  ) {
		
		// Get stored plugin options
		$options = get_option('ffg_options');
		
		// Set the cache folder
		$this->folder = $options['cache_folder'];
		
	}
	
	/* - - - - - -
		
		Does the whole process.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
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
	
	/* - - - - - -
		
		Get the file name.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function getName( $file ) {
		return $this->prefix . md5($file) . ".json";
	}
	
	
	/* - - - - - -
		
		Check to see if a file is cached
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function isCached( $file, $expires ) {		
		
		// Get file path/name.
		$cache_file = $this->folder . $this->getName($file);
		
		// When was the file created.
		$cachefile_created = ( file_exists($cache_file) ) ? @filemtime($cache_file) : 0;
					
		// Return if it's valid or not.
		return ( (time() - $expires) < $cachefile_created );
	}
	
	
	/* - - - - - -
		
		Get a file
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function getCache( $file ) {
		$cache_file = $this->folder . $this->getName($file);
		
		return file_get_contents($cache_file);
	}
	
	
	/* - - - - - -
		
		Caches a file
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function saveCache($file, $content) {
		
		$cache_file = $this->folder . $this->getName($file);
		
		// See if there is a cache folder and that it's writable. 
		if ( wp_mkdir_p($this->cache_folder) && ( ! file_exists($cache_file) || is_writable($cache_file) ) ) {
		
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