<?php namespace Plugin;

use Phalcon\DI;
use Phalcon\Events\Event,
	Phalcon\Mvc\User\Plugin,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Acl;
use Phalcon\Exception;

/**
 * Security
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class Security extends Plugin
{
	/**
	 * @var \Phalcon\Acl\Adapter\Memory
	 */
	private $acl;

	/**
	 * @var DI
	 */
	private $di;

	public function __construct( DI $di){
		$this->di = $di;
	}

	/**
	 * @return Acl\Adapter\Memory
	 */
	public function getAcl(){
		if(is_object($this->acl)){
			return $this->acl;
		}

		$this->acl = $this->rebuildAcl();
		/* CACHE ACL
		// Check if the ACL is in APC
		if (function_exists('apc_fetch')) {
			$acl = apc_fetch('mia-acl');
			if (is_object($acl)) {
				$this->acl = $acl;
				return $acl;
			}
		}

		// Check if the ACL is already generated
		if (!file_exists(APP_DIR . $this->filePath)) {
			$this->acl = $this->rebuildAcl();
			return $this->acl;
		}

		// Get the ACL from the data file
		$data = file_get_contents(APP_DIR . $this->filePath);
		$this->acl = unserialize($data);

		// Store the ACL in APC
		if (function_exists('apc_store')) {
			apc_store('mia-acl', $this->acl);
		}*/

		return $this->acl;
	}

	/**
	 * This action is executed before execute any action in the application
	 * @param $event
	 * @param $dispatcher
	 * @return boolean
	 */
	public function beforeDispatch(Event $event, Dispatcher $dispatcher)
	{
		$session = $this->di->getShared('session')->get('auth');
		/*if(!$session) {
			$this->di->getShared('response')->redirect('auth/login')->send();
		}*/

		$role = $session['role'];

		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();
		$currentModule = ucfirst($dispatcher->getModuleName());

		$allowed = $this->isAllowed($role, $currentModule, $controller, $action);

		if ($allowed !== Acl::ALLOW) {
			$this->flash->error('You don\'t have access to this module! <i class="remove"></i>');
			if(strtolower($currentModule) !== 'auth' && strtolower($action) !== 'login'){
			    $this->di->getShared('response')->redirect('auth/login')->send();
			}
		}
	}

	public function isAllowed( $role, $module, $controller, $action ){
		$this->di->getSession()->set('auth', array(
			'role' => 'superadmin',
			'email' => 'dan'
			));
		if( $module === 'Auth' && $controller === 'login' || $controller == 'permissions') {
			return true;
		}
		
		$module = strtolower($module);
		$acl = $this->getAcl();
		$auth = $this->di->getAuth(); //neregistrovat auth jako service, jedna se o cast ktera neni k vyuziti napric cele aplikaci ale pouze jednou
		return $acl->isAllowed($role, "$module-$controller", $action) && $auth->check();
	}

	/**
	 *
	 */
	public function rebuildAcl() {
		$acl = new Acl\Adapter\Memory();
		$acl->setDefaultAction(ACL::DENY);

		/*$db = $this->di->get('db');
		$roles = $db->fetchAll("SELECT name FROM role");*/
		$roles = array(
			array(
				'name' => 'client'
			),
			array(
				'name' => 'admin'
			),
			array(
				'name' => 'superadmin'
				)
		);
		foreach($roles as $role) {
			$acl->addRole( new Acl\Role($role['name']) );
		}
		$modulesResources = $this->di->getConfig()->resources;
		foreach($modulesResources as $module => $res) {
			$controllers = array_keys( (array) $res[0] );
			foreach($controllers as $controller) {
				$resource = "$module-$controller";
				$actions = array_map(function($a) {
				    return (array) $a;				    
				}, (array) $res[0]);				
				
				$acl->addResource( new Acl\Resource($resource), $actions[$controller] );
			}
		}
		
		$acl->allow('superadmin', 'auth-permissions', array('index'));
		$acl->allow('client', 'auth-login', array('index', 'process'));
		$acl->allow('client', 'auth-logout', array('index'));
		$acl->allow('superadmin', 'auth-signup', array('index'));
		$acl->allow('superadmin', 'dashboard-index', array('index'));
		$acl->allow('superadmin', 'users-index', array('index'));
		$acl->allow('superadmin', 'users-index', array('add', 'delete'));
		$acl->allow('admin', 'dashboard-index', array('index'));
		// $acl->allow('client', 'frontend-index', 'index');
		// $acl->allow('superadmin', 'admin-index', 'index');

		return $acl;
	}

}
