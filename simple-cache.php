<?php

/**
 * 
 * 
 * Simple Cache
 * 
 */

class SimpleCache{

    protected $dataFunction;
    
    protected $expiryInterval;
    
    protected $filePath;
    
    protected $cache;
    
    /**
     * 
     * @param string $liveDataFunction The user-defined function 
     * that returns the data to be cached (must be a serializable string). 
     * Note: If not defined, this class assumes an OOP implementation and should be extended with 
     * the getLiveData() method overridden.
     * @param int $cacheExpiry [optional] the timer (in seconds) during which live 
     * data will be served from the cache file instead. After this timer ends, and the cache expires, 
     * the next request will refresh the cache with live data and reset the timer. The default is 30 seconds.
     * @param string $cacheFile [optional] the file where the cache data will be stored. 
     * The default is 'cache' in the script directory.
     */
    public function __construct($liveDataFunction = null, $cacheExpiry = 30, $cacheFile = 'cache') {
        
        //load properties
        $this->dataFunction = $liveDataFunction;
        $this->expiryInterval = $cacheExpiry;
        $this->filePath = __DIR__.'/'.$cacheFile;
        $this->_loadCache();
        
    }
    
    /**
     * Gets the data from the cache file, unless it has 
     * expired (see the constructor's $cacheExpiry parameter). 
     * If the cache expired, this fetches live data and updates the cache (see the constructor's $liveDataFunction parameter).
     * @return string The data (as returned by the live data function).
     */
    public function getData(){
        
        if($this->_cacheHasExpired()){
            
            $this->cache = $this->_cache();
            
        }
        return $this->cache->data;
    }
    
    /**
     * 
     * @return boolean whether the cache is still valid or not
     */
    private function _cacheHasExpired(){
        
        $expiryTime = time() - $this->expiryInterval;//the time when the cache expires
        
        if($this->cache->time < $expiryTime){
            
            return true;
            
        }
        else return false;
        
    }
    
    /**
     * Caches the live data
     * @return \stdClass An object with the cache data and timestamp.
     */
    private function _cache(){
        
        //get the live data
        $data = $this->getLiveData();
        
        //store the live data
        $cacheObject = $this->_storeData($data);
        
        //return the fresh cache 
        return $cacheObject;
        
    }
    
    /**
     * Stores the data into a file
     * @param string $data The (serializable) data string to store.
     * @return \stdClass An object with the stored data and timestamp.
     */
    private function _storeData($data) {
        
        //create a new cache object
        $cacheObject = new stdClass();
        $cacheObject->time = time();//current time
        $cacheObject->data = $data;
        
        //serialize the object for storage
        $fileContents = serialize($cacheObject);
        
        //write the the contents to a file (even if the dir doesn't exist, unlike php's file_put_contents)
        self::_file_force_contents($this->filePath, $fileContents);
        
        return $cacheObject;
        
    }

    /**
     * Loads the cache from the file. If the file doesn't exist, it forces a new cache.
     */
    private function _loadCache() {
        
        if(file_exists($this->filePath)){//cache file exists, load its contents
            
            //read the contents in the file
            $fileContents = file_get_contents($this->filePath);
            
            //recreate the cache object
            $cacheObject = unserialize($fileContents);
            
        }else{//no cache file

            //cache the live data
            $cacheObject = $this->_cache();
            
        }
        
        //load the cache object
        $this->cache = $cacheObject;
        
    }
    
    /**
     * Fetches the live data by calling your live data function (see the constructor's 
     * $liveDataFunction parameter). Note: in the OOP implementation, this method must be 
     * overridden with your live data fetching code.
     * @return string a serializable string containing the data.
     */
    protected function getLiveData(){
        
        if(!function_exists($this->dataFunction)){
            
            trigger_error('No data function found. ', E_USER_WARNING);
            
        }
        else return call_user_func($this->dataFunction);
        
    }
    
    /**
     * File put contents fails if you try to put a file in a directory that doesn't exist. 
     * This creates the directory. (Taken from the user notes in http://php.net/manual/en/function.file-put-contents.php)
     * @param string $dir 
     * @param string $contents
     */
    private static function _file_force_contents($dir, $contents){
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        foreach($parts as $part)
            if(!is_dir($dir .= "/$part")) mkdir($dir);
        file_put_contents("$dir/$file", $contents);
    }
    
    
}

?>
