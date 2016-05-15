<?php
namespace Core\Directory;

use Core\Directory\Reader;

/**
 * Description of Manager
 *
 * @author softdream
 */
class Manager {
    //put your code here
    /**
     * @var \Core\Directory\Reader
     */
    protected $reader;
    
    public function __construct($directory,$exceptionOnFalse = true){
        $this->reader = new Reader($directory,$exceptionOnFalse);
    }
    
    public function isWritable(){
        $path = $this->reader->getPath();
        $path = (substr($path, 0,1) === '/') ? substr($path, 1) : $path;
        return is_writable($path);
    }
    
    public function isExist(){
        $path = $this->reader->getPath();
        return file_exists($path);
    }
    
    /**
     * @param string $name directory name
     * @param string $mask directory permissions ( 0777 = writable from all, default = 0766 )
     * @return boolean|\Core\Directory\Manager False when directory isn't created, Object Manager when successful
     */
    public function createDirectory($name,$mask = '0766'){
        
        if(!$this->isWritable()){
            throw new \Core\Directory\Exception("Path ".$this->reader->getPath().' is not writable.');
        }
        
        $path = $this->reader->getPath();
        if(!mkdir($path.$name, $mask)){
            return false;
        }
        
        return new self($path.$name.'/');
    }
    
    public function __call($name, $arguments) {
        return call_user_func_array(array($this->reader,$name), $arguments);
    }
}
