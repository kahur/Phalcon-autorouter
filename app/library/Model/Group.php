<?php
namespace Model;

/**
 * Description of Group
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Group extends \Core\Model {
    public $id;
    public $name;
    public $created_at;
    public $updated_at;
    public $public;
    public $delete;
    
    public function initialize(){
        
        $this->hasMany('id', '\Model\Permissions', 'group_id');
        
        
        
        if(!$this->id){
            $this->created_at = date("Y-m-d H:i:s",time());
        }
        else {
            $this->updated_at = date("Y-m-d H:i:s",time());
        }
    }
}

