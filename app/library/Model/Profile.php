<?php
namespace Model;

/**
 * Description of Profile
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Profile extends \Core\Model {
    
    public $id;
    public $user_id;
    public $nickname;
    public $forename;
    public $surname;
    public $street;
    public $postal;
    public $city;
    public $IC;
    public $DIC;
    public $avatar;
    public $created_at;
    public $updated_at;
    
    public function initialize(){
        $this->hasOne('user_id', '\Model\User', 'id',array('foreignKey'  => true));
    }
    
       
}

