<?php namespace Core;

use Phalcon\Mvc\User\Component;
use Model\User;

class Auth extends Component {
    private static $instance;

    protected $session;

    protected $identity;
    
    protected $authObject;

    private function __construct() {
        $this->session = $this->di->getSession();
    }
    
    /**
     * @return \Model\User
     */
    public static function getUser(){
        return self::getInstance()->getAuthObject();
    }
    /**
     * @return \Core\Auth
     */
    private static function getInstance(){
        if( !self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }
    
    public function setAuthObject($user){
//        $user->password = '';
        $this->authObject = $user;
    }
    
    /**
     * @return \Model\User
     */
    public function getAuthObject(){
        return $this->authObject;
    }
    
    /**
     * Authenticate user, when user will be authenticated than $identity will be created
     * @return \Core\Auth return created Auth object
     */
    public static function authenticate($email, $password, $isHashed = false)
    {
        $user = User::findFirst(array(
                "email = :email:",
                "bind" => array(
                    "email" => $email
                )
            )
        );
        
        
        $obj = self::getInstance();
        $obj->setAuthObject($user);
        if($user !== false)
        {
	    if($user->email_verification == 0){
		$obj->flash->notice('Tento účet nebyl ověřen emailem.<br />Pokud Vám neodrazil email nechte si jej zaslat znova kliknutím na následujíci odkaz:<br />'.\Phalcon\Tag::linkTo('auth/resend-verification/'.$user->id.'/','znovu odelsat ověřovací email').'.');
		return $obj;
	    }
            $security = $obj->di->getSecurity();
            if( $security->checkHash($password, $user->password) || ($isHashed && $user->password === $password) ) {
                $obj->setIdentity($user);
            }
        }
        else {
            $obj->flash->notice("Neexistujíci uživatel.");
        }
        
        return $obj;
    }

    public static function logout()
    {
        self::getInstance()->removeIdentity();
    }

    /**
     * @return stdClass|null Created identity 
     */
    public static function getIdentity(){
        $auth = Auth::getInstance();
        if(!self::getInstance()->identity) {
            $auth->identity = $auth->session->get('authUser');
        }
        
//        var_dump($auth);
        
        return $auth->identity;
    }
    
    public static function getUserId(){
        $identity = self::getIdentity();
        return ($identity->group_id > 2 && $identity->parent_id) ? $identity->parent_id : $identity->id;
    }

    protected function setIdentity(\Model\User $user){
        $group = $user->getRelated('group');
        $this->identity = new \stdClass();
        $this->identity->id = $user->id;
        $this->identity->group_id = $user->group_id;
        $this->identity->state = 'online';
        $this->identity->role = $group->name;
        $this->identity->name = $user->name;
        $this->identity->forename = $user->profile->forename;
        $this->identity->surname = $user->profile->surname;
        $this->identity->fb_id = $user->fb_id;
    }
    
    public static function updateIdentity(array $data){
        $obj = self::getInstance();
        
        foreach($data as $var => $value){
            $obj->identity->$var = $value;
        }
        
        
        $obj->writeIdentity();
    }

    public function writeIdentity()
    {
        $this->session->set('authUser', $this->identity);
    }

    public function hasIdentity(){
        return isset($this->identity);
    }

    protected function removeIdentity()
    {
        $this->identity = null;
        $this->session->remove('authUser');
    }
}