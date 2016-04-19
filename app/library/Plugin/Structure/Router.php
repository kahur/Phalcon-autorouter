<?php
namespace Plugin\Structure;

/**
 * Description of Router
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Router {
    //put your code here
    protected $route;
    
    protected $object;
    protected $method = 'index';
    public function __construct($definition = null){
        if($definition){
            $parts = explode(":",$definition);

            $class = '\Plugin\Structure\Controller\\'.ucfirst(strtolower($parts[0]));
            $this->object = new $class;
            $this->method = $parts[1];
        }
        else {
            $this->object = new \Plugin\Structure\Controller\Index;
        }
    }
    
    public function dispatch(){
        $name = $this->method;
        $this->object->$name();
    }
}
