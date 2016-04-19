<?php
namespace Core;

/**
 * Description of Observer
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/FlipFlop/license/
 * @version 1.0
 */
class Observer {
    //put your code here
    
    /**
     * List of registered observers
     * @var \Observer\Server[] array
     */
    protected $observers = array();
    
    /**
     * List of unassigned listeners waiting for observer registration
     * @var array
     */
    protected $listeners = array();
    /**
     * List of observers map
     * @var array
     */
    protected $map;
    /**
     * @var \Core\Observer single uniqe instance of observer
     */
    protected static $instance;
    private function __construct(){}
    
    /**
     * @return \Core\Observer Description
     */
    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function getObservers(){
        return $this->observers;
    }
    
    /**
     * Register observer
     * @param String $module Description
     * @param String $name Description
     */
    public function addObserver($name){
        $observer = new Observer\Server($name);
        if(isset($this->listeners[$name])){
            foreach($this->listeners[$name] as $listener){
                $observer->addListener($listener);
            }
            unset($this->listeners[$name]);
        }
        
        if(!in_array($observer, $this->observers) && !isset($this->map[$name])){
            $this->observers[] = $observer;

            $this->map[$name] = count($this->observers)-1;
        }
    }
    
    public function hasObserver($name){
        return isset($this->map[$name]);
    }
    
    /**
     * Register observer
     * @param String $module Description
     * @param String $observerName Description
     */
    public static function registerObserver($observerName){
        $obj = self::getInstance();
        $obj->addObserver($observerName);
    }
    
    public static function registerListener($listener,$callback){
        $observer = self::getInstance();
        $observer->addListener($listener, $callback);
    }
    
    public function addListener($listener, $callback){
        if(isset($this->map[$listener]) && isset($this->observers[$this->map[$listener]])){
            $observer = $this->observers[$this->map[$listener]];
            $observer->addListener($callback);
        }
        else {
            if(!isset($this->listeners[$listener])){
                $this->listeners[$listener] = array();                
            }
            
            $this->listeners[$listener][] = $callback;
        }
    }
    
    /**
     * Run observer handler
     * @param String $type before|after|null
     */
    public static function run($type = 'before'){
        $called = debug_backtrace()[1];
        $class = $called['class'];
        $classParts = explode("\\",$class);
        $module = $classParts[0];
        $resource = str_replace('Controller','',$classParts[2]);
        $action = str_replace("Action","",$called['function']);
        
        $observer = strtolower($module.'.'.$resource.'.'.$type).ucfirst($action);
        
        $object = self::getInstance();
        if($object->hasObserver($observer)){
            $object->call($observer);
        }
    }
    
    /**
     * Run custom defined observer
     * @param String $name Description
     */
    public static function runObserver($name){
        $observer = self::getInstance();
        if($observer->hasObserver($name)){
            $observer->call($name);
        }
    }
    
    public function call($observer){
        $server = $this->observers[$this->map[$observer]];
        $listeners = $server->getListeners();
        foreach($listeners as $observ){
            if($observ instanceof Observer\Server\Func){
                $observ->call();
            }
            else if(isset($observ['object']) && isset($observ['function'])){
                if(strpos($observ['function'], '::')){
                    $observ['object']::$observ['function']();
                }
                else {
                    $serv = new $observ['object']();
                    $serv->$observ['function']();
                }
            }
            
        }
    }
    
    
    /**
     * Prepare server observer run
     */
    public static function start(){}
    
    public static function stop(){}
    
    public function listenersTo(){
        var_dump($this->listeners);
        foreach($this->listeners as $key => $fn){
            echo $key."<br />";
        }
    }
}
