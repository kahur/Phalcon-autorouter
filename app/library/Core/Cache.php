<?php
/**
 * Description of Cache
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Core, Webvizitky
 * @package name
 * @category name
 * 
 */
namespace Core;

use Phalcon\Cache\Frontend,
	Phalcon\Cache\Backend;

class Cache {
    
    /**
     * @param String $frontType Data|Output|Base64|None|Igbinary
     * @param String $storage Memcache|Mongo|Memory|Apc|File|Libmemcached|Xcache
     * @return \Phalcon\Cache\Backend Description
     * @throws \Core\Cache\Exception
     */
    public static function factory($frontType,$storage,array $options = array()){
        
	$front = '\Phalcon\Cache\Frontend\\'.ucfirst(strtolower($frontType));
	
	if(!class_exists($front)){
	    throw new \Core\Cache\Exception("Cache frontend ".$frontType." is not supported.");
	}
	
	$backend = '\Phalcon\Cache\Backend\\'.ucfirst(strtolower($storage));
	if(!class_exists($backend)){
	    throw new \Core\Cache\Exception("Cache backend ".$storage." is not supported.");
	}
	 //set default lifetime
	if(!isset($options['lifetime'])){
	    $options['lifetime'] = 172800;
	}
	
	$frontCache = new $front(array('lifetime'   => $options['lifetime']));
	unset($options['lifetime']);
	$backendCache = new $backend($frontCache,$options);
	
	return $backendCache;
	
     }
     
     public static function cleanUp(\Phalcon\Cache\Backend $cache){
         
         $keys = $cache->get('keys');
         foreach($keys as $key){
             $cache->delete($key);
         }
     }
}
