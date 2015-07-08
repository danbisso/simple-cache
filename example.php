<?php
        
//include our simple cache class
include_once 'simple-cache.php';

//create the cache object, setting my_data as the live data function
$cache= new SimpleCache('my_data');

//give me the data!!
echo $cache->getData();


/**
 * This is the function that returns the live data. Put your code in here.
 * Note: for the cache to work, this must return a serializable string. 
 * See http://php.net/manual/en/function.serialize.php
 */
function my_data() {

    return 'some serializable data';

}
?>