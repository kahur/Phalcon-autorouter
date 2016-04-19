<?php
namespace Core\Observer\Server;


/**
 * Description of Function
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/FlipFlop/license/
 * @version 1.0
 */
class Func {
//put your code here
    protected $function;
    public function __construct($item){
        $this->function = $item;
    }
    
    public function __toString() {
        return $this->call();
    }
    
    public function call(){
        $callable = $this->function;
        return $callable();
    }
}
