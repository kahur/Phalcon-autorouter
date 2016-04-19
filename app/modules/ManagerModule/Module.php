<?php

namespace Manager;

class Module extends \BaseModule { 
     public function __construct() {
         
	 parent::__construct(__NAMESPACE__);
     }
     
     public function registerServices(\Phalcon\DiInterface $di) {
         parent::registerServices($di);
         
        $di->set("moduleControl",function(){
            return new \Core\Component\Module();
        });
     }
}