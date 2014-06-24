<?php
namespace Softdream\Http\Url;

/**
 * Description of Map
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Map {
    protected $map;
    public function __construct($urlMap){
	preg_match_all("@:([A-Za-z0-9._-]+)@s",$urlMap,$matches);
	if(isset($matches[1])){
	    $this->map = $matches[1];
	}
    }
        
    public function getKey($name){
	return array_search($name, $this->map);
    }
    
}

