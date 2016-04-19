<?php
namespace Model;
/**
 * Description of Message
 *
 * @author softdream
 */
class Message extends \Core\Model{
    //put your code here
    public $id;
    public $site_id;
    public $visitor_id;
    public $user_id;
    public $message;
    public $response;
    public $created_at;
    public $updated_at;
    
    public function beforeSave(){
        $this->created_at = date("Y-m-d H:i:s",time());
    }
    
    public function beforeUpdate(){
        $this->updated_at = date("Y-m-d H:i:s",time());
    }
}
