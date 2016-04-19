<?php
namespace Model;

use Phalcon\Exception;

/**
 * Description of User
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */

class User extends \Core\Model {
    public $id;
    public $group_id;
    public $name;
    public $email;
    public $password;
    public $created_at;
    public $updated_at;
    public $request_hash;
    public $request_hash_expiration;
    public $active;
    public $email_verification;
    public $parent_user_id;
    public $last_activity;
    public $online_state;
    public $oldEmail;
    public $auth;
    public $fb_id;
    protected $filters;
    public function initialize()
    {        
    	$this->hasOne('group_id', '\Model\Group', 'id',array(
            'alias' => 'group'
        ));
        
        $this->hasOne("id", "\Model\Operator\Attributes","user_id",array(
           'alias' => 'operatorAttributes'
        ));
        
        $this->hasOne("id","\Model\User\Settings","user_id", array(
            'alias' => 'settings'
        ));
        
        $this->hasOne('id', '\Model\Profile', 'user_id',array(
            'alias' => 'profile',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
    }
    
    
    public function isValid(){
        if(!$this->id || ($this->id && $this->email !== $this->oldEmail))
        {
            $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array(
                'field'	=> 'email',
                'message'	=> 'Uživatel s tímto emailem již existuje.'
            )));
        }
	
	return $this->validationHasFailed() ? false : true;
    }
    
    public function findUserAndProfile(){
        $criteria = $this->getCriteria();
//        $criteria->set
        $this->addColumn('Model\User.id')
             ->addColumn('email')
             ->addColumn('active')
             ->addColumn('Model\User.created_at')
             ->addColumn('p.forename')
             ->addColumn('p.surname')
             ->addColumn('g.name as role');
        
        $criteria->join('Model\Group','group_id = g.id','g');
        $criteria->leftJoin('Model\Profile', 'Model\User.id = p.user_id', 'p');
//        $criteria->join
        
        return $this->findFiltered();
    }
    
}
