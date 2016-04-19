<?php
namespace Core\Http;

/**
 * Description of Request
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Request extends \Phalcon\Http\Request {
    protected $uri;
    /**
     * @var \Core\Http\Request\Map Description
     */
    protected $uriMap;
    /**
     * @var array $_GET items
     */
    protected $items = array();
    /**
     * @var array uriParts
     */
    protected $uriParts;
    public function __construct(\Core\Http\Url\Map $map = null) {
	$this->uri = $this->getURI();
	$this->items = $_GET;
	$this->parseUri();
	
	if($map !== null){
	    $this->setMap($map);
	}
	
	
	
    }
    
    public function setMap($map){
        if(!$map instanceof \Core\Http\Url\Map){
            $map = new Url\Map($map);
        }
        
        $this->uriMap = $map;
    }
    
    public function parseUri(){		
	
	if($this->uri !== '/')
	{
	    $prevPart = null;
	    $this->uriParts = explode("/",substr($this->uri,1));
	    foreach($this->uriParts as $part){
		if($prevPart === null){
		    $prevPart = $part;
		}
		else {
		    $this->items[$prevPart]=  $part;
		    $prevPart = null;
		}
	    }
	}
    }
    
    public function __isset($name) {
	return isset($this->items[$name]);
    }
    
    public function __get($name) {
	$this->getParam($name);
    }
    
    public function getUriParts(){
	return $this->uriParts;
    }
    
    public function getParam($name,$useMap = true) {
	if($this->uriMap && $useMap){
	    $key = $this->uriMap->getKey($name);
	    $value = null;
	    if($key !== false){
		$value = isset($this->uriParts[$key]) ? $this->uriParts[$key] : null;
	    }
	    
	    if(!$value){
		$value = isset($this->items[$name]) ? $this->items[$name] : null;
	    }
	    
	    return $value;
	}
	else {
	    return isset($this->items[$name]) ? $this->items[$name] : null;
	}
    }
    
    public function getParams(){	
	return $this->items;
    }
    
    public function getMap(){
	return $this->uriMap;
    }
    
    public function removeParam($key,$useMap = true){
	$k = false;
	if($this->uriMap && $useMap === true){
	    $k = $this->uriMap->getKey($key);
	    if($k === null){
		$k = $key;
		$this->removeByTextKey($k);
	    }
	    else {
		$this->removeByKey($k);
	    }
	}
	else if(!is_numeric($key)) {
	    $k = array_search($key, $this->uriParts);
	    $this->removeByKey($k);
	}
	
    }
    
    protected function removeByKey($key){
	if(is_numeric($key)){
	    //remove first text param
	    $textKey = isset($this->uriParts[$key]) ? $this->uriParts[$key] : false;
	    if($textKey){
		$pos = strpos($this->uri, '/'.$textKey);
		$this->uri = substr_replace($this->uri,'',$pos,strlen('/'.$textKey));
		unset($this->uriParts[$key]);
		unset($this->items[$textKey]);
		if($this->uriMap){
		    $this->uriMap->removeItemFromMap($key);
		}
		array_splice($this->uriParts, 0, 0);
	    }
	    
	}
    }

    
    
    
    protected function removeByTextKey($key){
	if(!is_numeric($key)){
	    $k = array_search($key, $this->uriParts);
	    $this->removeByKey($k);
	}
    }
    
    public function removeMap(){
	$this->uriMap = null;
    }
    
    public function clearItems(){
	$this->items = array();
	$this->uriParts = array();
    }
    
}

