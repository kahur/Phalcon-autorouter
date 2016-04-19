<?php
namespace Core\Observer;

/**
 * Description of Server
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/FlipFlop/license/
 * @version 1.0
 */
class Server {
    //put your code here
    protected $listeners = array();
    protected $name;
    
    protected static $instance;
    
    public function __construct($name) {
        $this->name = $name;
        self::$instance = $this;
    }
    
    public function addListener($listener){
        if(is_callable($listener)){
            $listener = new Server\Func($listener);
        }
        
        $this->listeners[] = $listener;
        
        return count($this->listeners);
    }
    
    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function getListeners(){
        return $this->listeners;
    }
}
