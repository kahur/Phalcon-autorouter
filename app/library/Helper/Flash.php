<?php
namespace Helper;
/**
 * Description of Flash
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Flash {
    protected static $instance;
    
    /**
     * @var \Phalcon\Flash flash adapter
     */
    protected $adapter;
    
    private function __construct() {
	
    }
    
    /**
     * Get created instance of object
     * @return \Helper\Flash Description
     */
    protected static function getInstance(){
	if(!self::$instance){
	    self::$instance = new self();
	}
	
	return self::$instance;
    }
    
    public function setAdapter($adapter){
	$this->adapter = $adapter;
    }
    
    /**
     * @return \Phalcon\Flash Description
     */
    public function getAdapter(){
	return $this->adapter;
    }
    
    public function init(){
	if(!$this->adapter){
	    throw new \Exception('Adapter is not set.');
	}
    }
    
    public static function registerAdapter(\Phalcon\Flash $adapter){
	$instance = self::getInstance();
	if(!$instance->getAdapter()){
	    $instance->setAdapter($adapter);
	}
    }
    
    public static function warning($message, \Phalcon\Flash $adapter = null){
	
	$instance = self::getInstance();
	if($adapter && !$instance->getAdapter()){
	    $instance->registerAdapter($adapter);
	}
	$instance->init();
	
	$adapter = $instance->getAdapter();
	foreach($message as $msg){
	    $adapter->warning($msg);
	}
	
    }
    
    public static function notice($message, \Phalcon\Flash $adapter = null){
	$instance = self::getInstance();
	if($adapter && !$instance->getAdapter()){
	    $instance->registerAdapter($adapter);
	}
	
	$instance->init();
	
	$adapter = $instance->getAdapter();
	foreach($message as $msg){
	    $adapter->notice($msg);
	}
    }
    public static function success($message, \Phalcon\Adapter $adapter = null){
	$instance = self::getInstance();
	if($adapter && !$instance->getAdapter()){
	    $instance->registerAdapter($adapter);
	}
	$instance->init();
	
	$adapter = $instance->getAdapter();
	foreach($message as $msg){
	    $adapter->success($msg);
	}
    }
    public static function error($message, \Phalcon\Flash $adapter = null){
	$instance = self::getInstance();
	if($adapter && !$instance->getAdapter()){
	    $instance->registerAdapter($adapter);
	}
	$instance->init();
	
	$adapter = $instance->getAdapter();
	foreach($message as $msg){
	    $adapter->error($msg);
	}
    }
}

