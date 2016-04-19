<?php
namespace Core;

/**
 * Description of Share
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Share extends \Phalcon\Mvc\User\Component {
    //put your code here
    protected $shared;
    protected $protection;
    public function __construct(){}
    
    public function addItem($id,$item){
        
        $this->shared[$id] = $item;
        if($sharedFor){
            $callers[$id] = $sharedFor;
        }
    }
    
    /**
     * @param String $id Identification of item, where is possible to get shared item
     * @param String sharedTo|uri|pass sharedTo is possible to set object where is need to be called in, in url you can pass url where can be accessed this shared object, pass to protect item by password when password is wrong the item will not be returned
     * @param mixed value of item
     */
    public function setSecurityItem($id,$type,$value){        
            $this->protection[$id] = array(
                'type' => $type,
                'value' => $value
            );        
    }
    
    public function getItem($id,$password = null){
        $valid = true;
        if(isset($this->protection[$id])){
            $protection = $this->protection[$id]['type'];
            if($protection === 'sharedTo'){
                $valid = $this->checkCalledClass($this->protection[$id]);
            }
            else if($protection === 'url'){
                $valid = $this->checkUri($this->protection[$id]['value']);
            }
            else if($protection === 'pass'){
                $valid = $this->checkPass($password,$this->protection[$id]['value']);
            }
            else {
                $valid = false;
            }            
        }
        
        return ($valid) ? $this->protection[$id] : null;        
    }
    
    protected function checkCalledClass(array $protection){
        $class = get_called_class();
        if($class === $protection['value']){
            return true;
        }
        
        return false;
    }
    
    protected function checkUri($value){
        if($this->request->getURI() === $value){
            return true;
        }
        
        return false;
    }
    
    protected function checkPass($pass,$value){
        if($pass === $value){
            return true;
        }
        
        return false;
    }
}
