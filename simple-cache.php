<?php

/*
 * 
 * Simple Cache
 * 
 */


abstract class SimpleCache{
    
    protected $expiryInterval;
    
    protected $filePath;
    
    protected $cache;
    
    
    public function __construct($cacheExpiry = 30, $cacheFile = 'simple-cache/cache') {
        
        //load properties
        $this->expiryInterval = $cacheExpiry;
        $this->filePath = $cacheFile;
        $this->loadCacheData();
        
    }
    
    public function getData(){
        
        if($this->cacheHasExpired()){
            
            $this->cache = $this->updateCache();
            
        }
        return $this->cache->data;
    }
    
    protected function cacheHasExpired(){
        
        $expiryTime = time() - $this->expiryInterval;//the time when the cache expires
        
        if($this->cache->time < $expiryTime){
            
            return true;
            
        }
        else return false;
        
    }
    
    protected function updateCache(){
        
        //get the live data
        $data = $this->getLiveData();
        
        //store the live data
        $cacheObject = $this->storeData($data);
        
        //return the new cache 
        return $cacheObject;
        
    }
    
    protected function storeData($data) {
        
        //create a new cache object
        $cacheObject = new stdClass();
        $cacheObject->time = time();//current time
        $cacheObject->data = $data;
        
        //serialize the object for storage
        $fileContents = serialize($cacheObject);
        
        //write the the contents to a file
        file_put_contents($this->filePath, $fileContents);
        
        return $cacheObject;
        
    }

    protected function loadCacheData() {
        
        if(file_exists($this->filePath)){//cache file exists, load its contents
            
            //read the contents in the file
            $fileContents = file_get_contents($this->filePath);
            
            //recreate the cache object
            $cacheObject = unserialize($fileContents);
            
        }else{//no cache file, load live data and then save that into a file
            
            //get the live data and load it
            $liveData = $this->getLiveData();
            
            //store the live data
            $cacheObject = $this->storeData($liveData);
            
        }
        
        //load the cache object
        $this->cache = $cacheObject;
        
    }
    
    protected abstract function getLiveData();
    
}

?>
