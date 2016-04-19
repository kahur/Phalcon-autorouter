<?php
namespace Model;

/**
 * Description of Newsletter
 *
 * @author webdev
 */
class Newsletter extends \Core\Model {
    //put your code here
    public $id;
    public $group_id;
    public $title;
    public $body;
    public $created_at;
    public $sended_to;
    
    public function getUsers(){
        $groupId = 3;
        if($this->group_id){
            $group = Group::findFirst($this->group_id);
            $groupId = $group->id;
        }
        
        return User::find(array(
            'group_id = :group:',
            'bind' => array(
                'group' => $groupId
            )
        ));
    }
}
    
