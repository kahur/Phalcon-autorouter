<?php
namespace Core\Directory;

/**
 * Description of Reader
 *  - add support to read windows folders
 *  - getDirParts returns windows or unix dir parts
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * @version 1.1
 * 
 */
class Reader {
    
    protected static $actualInstance;
    
    protected $handler;
    protected $path;
    protected $dirName;
    public function __construct($path,$exceptionOnFalse = true) {
        if(substr($path,(strlen($path)-1),1) === '/'){
	    $this->path = substr($path,0,(strlen($path)-1));
	}
	else {
	    $this->path = $path;
	}
        
        $checkPath = (substr($this->path,0,1) === '/') ? substr($this->path, 1) : $this->path;
	if(!is_dir($checkPath) && !is_dir('/'.$checkPath)){
            if($exceptionOnFalse){
                throw new \Core\Directory\Exception('Directory "'.$path.'" doesnt exists or is not a directory. ');
            }
            else {
                return false;
            }
	}
	else {		
            $this->handler = dir(realpath($this->path));	
        }
        
        $dirParts = explode('/',$this->path);
	$this->dirName = end($dirParts);
	self::$actualInstance = $this;
    }
    
    public function read(){
        if(!$this->handler){
            return false;
        }
	return $this->handler->read();
    }
    
    public function getPath(){
        $path = $this->path;
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
            $path = str_replace("/","\\",$path).'\\';
            return $path;
        }
	return $this->path.'/';
    }
    
    public function getDirName(){
	return $this->dirName;
    }
    
    public function getDirParts(){
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
            $path = str_replace("/","\\",$this->path);
            $parts = explode("\\",$path);
//            array_shift($parts);
            return $parts;
        }
	return explode("/",$this->path);
    }
    
    /**
     * return actual instance
     * @return \Core\Directory\Reader Description
     */
    public static function getActualInstance(){
	return self::$actualInstance;
    }
}

