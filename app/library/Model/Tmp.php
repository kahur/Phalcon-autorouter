<?php
namespace Model;

/**
 * Description of Tmp
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Tmp extends \Core\Model {
    //put your code here
    public $id;
    public $items;
    public $user_id;
    public $url;
    public $created_at;
    
    public function setDefaults(){
        $data = unserialize($this->items);
        
        if(!$data){
            $data = array();
        }
        
        \Phalcon\Tag::setDefaults($data);
    }
}
