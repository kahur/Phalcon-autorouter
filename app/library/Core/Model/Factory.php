<?php
namespace Core\Model;

/**
 * Description of Factory
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Factory {
    //put your code here
    /**
     * @var \Core\Mode\Factory
     */
    protected static $factory;
    
    /**
     * @var \Core\Model[]
     */
    protected $instances;
    private function __construct() {
        ;
    }
    
    /**
     * Get self private instance
     * @return \Core\Model\Factory Description
     */
    protected static function factory(){
        if(!self::$factory){
            self::$factory = new self();
        }
        
        return self::$factory;
    }
    
    public function setInstance($class,$object){
        $this->instances[$class] = $object;
    }
    
    public function hasInstace($class){
        return isset($this->instances[$class]);
    }
    
    public function getInstance($class){
        return $this->instances[$class];
    }
    
    /**
     * @param String $class Full class name
     * @param boolean $shared Set if the class instance is shared and will be stored into sigle instance classes
     * @param boolean $newInstance true to return new instance of class, false to return shared instance of class if exists
     * @return \Core\Model
     */
    public static function create($class, $shared = true, $newInstance = false){
        if(!class_exists($class)){
            throw new Factory\Exception('Class '.$class.' doesnt exist');
        }
        $factory = self::factory();
        //check shared
        $object = ($factory->hasInstace($class)) ? $factory->getInstance($class) : null;
        
        if($newInstance || !$object){
            $object = new $class;
        }
        
        if($shared && !$factory->hasInstace($class)){
            $factory->setInstance($class, $object);
        }
        
        return $object;
    }
}
