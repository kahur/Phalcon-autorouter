<?php
namespace Plugin\Authentication;

use Phalcon\DI;
use Phalcon\Events\Event,
	Phalcon\Mvc\User\Plugin,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Acl;
use Phalcon\Mvc\Model;
use Phalcon\Exception;


/**
 * Description of AccessControl
 * Bugfixes and addons upon version 1.1:
 *  - Support to windows paths
 * 
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * @version 1.1
 */
class AccessControl extends Plugin {
    /**
     * Dependency injection container
     * @var \Phalcon\DI Description
     */
    protected $di;
    
    /**
     * AC allowed storage adapter ( Memory )
     * @var string
     */
    protected $adapterName;
    
    /**
     * List of role objects
     * @var array \Phalcon\Acl\Role list
     */
    protected $roles;
    
    /**
     * List of access rights
     * @var array 
     */
    protected $permisions;
    
    /**
     * List of module ACLs
     * @var array \Phalcon\Acl\Adapeter
     */
    
    protected $acl;
    
    /**
     * Modules list
     * @var array
     */
    protected $modules;
    
    /**
     * Application object
     * @var \Phalcon\Mvc\Application
     */
    protected $application;
    
    /**
     * Cache storage, when is false the cache is disabled
     * @var boolean
     */
    protected $cache = false;
    
    /**
     * Disable acl for modules
     * @var array
     */
    protected $disabledModules = array();
    
    /**
     * Real access list loaded from sources
     * @var array
     */
    protected $accessList = array();
    
    protected $callbacks = array();
    
    
    public function __construct(\Phalcon\Mvc\Application $app,$cache = false) {
	$this->adapterName = $this->getAdapterName();	
	$this->di = $app->getDI();
	$this->application = $app;
        
        if($cache){
            $this->enableCache();
        }
    }
    
    /**
     * Disable ACL controll in module
     * @param string $moduleName Module name
     */
    public function disableModuleAcl($moduleName){
        if(!in_array($moduleName, $this->disabledModules)){
            $this->disabledModules[] = $moduleName;
        }
    }
    
    /**
     * Enable support for cache info.
     */
    public function enableCache(){
        //check memcache
        if(fsockopen('127.0.0.1', 80, $errno, $errstr, 1)){
            $this->cache = \Core\Cache::factory('Data', 'Memcache');
        }
        else 
        {
            $this->cache = \Core\Cache::factory('Data', 'Apc');
        }
    }
    
    public function beforeException($event, $dispatcher, $exception)
    {
        throw $exception;
    }
    
    /**
     * Predefined method for application event handler
     * the method will be called before load and start 
     */   
    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher){
        
        $auth = \Core\Auth::getIdentity();
        if($auth){
            if(strtolower($dispatcher->getControllerName()) === 'auth' && strtolower($dispatcher->getActionName()) !== 'logout')
            {
                $this->response->redirect('')->send();
            }
            //cache goes here
            if($this->cache){
                $cache = \Core\Cache::factory('Data', 'Memcache');
//                $cache->delete('acl');
                if(!$cache->exists('acl'))
                {
                   $this->initAclData();
                   
                   $acl = new \Acl($this->acl);
                   $aclCache = $acl;
                   $cache->save('acl', $aclCache);
                }
                
                $acl = (isset($acl) && $acl instanceof \Acl) ? $acl : $cache->get('acl');
            }
            else {
                
                $this->initAclData();                
                $acl = new \Acl($this->acl);
            }
            
            $this->registerACL($acl);
            $module = ucfirst($dispatcher->getModuleName());
            $controller = str_replace('-','',strtolower($dispatcher->getControllerName()));
            $action = strtolower($dispatcher->getActionName());
            $role = isset($this->roles[$auth->group_id]) ? $this->roles[$auth->group_id]->getName() : null;
            $moduleAcl = $acl->getAcl($module);
            
            if(!in_array($module, $this->disabledModules)){
                
                if(!$role || !$moduleAcl || !$moduleAcl->isAllowed($role,$controller,$action)){
                    if(!$this->hasCallback($module)){
                        $this->flash->warning('Nemáte oprávnění na provedení této akce. <i class="remove"></i>');
                        if(!$moduleAcl->isAllowed($role, 'index', 'index')){
                            $auth = \Core\Auth::logout();
                        }

                        $this->response->redirect('/');
                    }
                    else {
                        $this->callModuleCallback($module);
                    }
                }
            }
        }
        
    }
    
    public function callModuleCallback($module){
        return $this->callbacks[strtolower($module)]();
    }
    
    public function hasCallback($module){
        if(is_callable($this->callbacks[strtolower($module)])){
            return true;
        }
        
        return false;
    }
    
    public function setAclModuleCallback($moduleName, $callback){
        if(is_callable($callback)){
            $this->callbacks[strtolower($moduleName)] = $callback;
            return true;
        }
        
        throw new \Exception("Callback is not a function");
    }
    
    protected function initAclData(){
        $this->loadApplicationInfo();
        $this->loadRoles();
        $this->loadPermisions();
        $this->setAclData();
    }
    
    protected function registerACL(\Acl $acl){
        $this->di->set('acl', function() use($acl){
            return $acl;
        });
    }
    
    private function setAclData(){
        array_walk($this->acl,function($acl,$module){  
            $this->addResources($acl, $module);
            $this->addRoles($acl);   
            $this->addPermisions($acl,$module);
        });
    }
    
    protected function addRoles(\Phalcon\Acl\Adapter $acl){
        foreach($this->roles as $role){
//            $acl->addRole
            $acl->addRole($role);
        }
    }
    
    protected function addPermisions(\Phalcon\Acl\Adapter $acl,$module){
            $permissions = isset($this->permisions[strtolower($module)]) ? $this->permisions[strtolower($module)] : null;
            if(!empty($permissions))
            {
                foreach($permissions as $permission){
                    $roleName = $this->roles[$permission['role']]->getName();
                    if($acl->isRole($roleName) && $acl->isResource(strtolower($permission['resource']))){
                        $accessList = $this->accessList[$permission['resource']];
//                        if(in_array(strtolower($permission['access']),$accessList)){
                            $acl->allow($roleName, strtolower($permission['resource']), strtolower($permission['access']));
//                        }
                    }
                }
            }        
            
            
    }
    
     protected function addResources(Acl\Adapter $acl,$module){
        $moduleInfo = $this->modules[$module];
        if(isset($moduleInfo['parts'])){
            foreach($moduleInfo['parts'] as $resource){
                $access = array();
                if(isset($resource['actions']) && !empty($resource['actions'])){
                    foreach($resource['actions'] as $action){
                        $access[] = strtolower($action['name']);
                    }
                    
                    $this->accessList[$resource['name']][] = strtolower($action['name']);
                }
                
                $acl->addResource(strtolower($resource['name']),$access);
            }
            
            
        }
     }
    

    /**
     * Load app information about modules
     */
    protected function loadApplicationInfo(){
	$modules = $this->application->getModules();
                
	$moduleInfo = array();
	foreach($modules as $name => $info){
                $moduleInfo[$name] = array(
                    'parts' => $this->loadModuleParts($info)
                );

                //register acl for module
                $acl = new Acl\Adapter\Memory();
                //when module is disabled set access to all parts
                if(in_array($name, $this->disabledModules)){
                    $acl->setDefaultAction(Acl::ALLOW);
                }
                else {
                    $acl->setDefaultAction(Acl::DENY);
                }
                
                $this->acl[$name] = $acl;           
	}
	
	$this->modules = $moduleInfo;
    }
    
   
    
    /**
     * Return all module info
     * @return array Description
     */
    public function getModules(){
        if(!$this->modules){
            $this->loadApplicationInfo();
        }
        return $this->modules;
    }
    
    /**
     * Load part of current module
     * @param array $moduleInfo Information about current module
     */
    protected function loadModuleParts(array $moduleInfo){
	$controllersPath = realpath(str_replace("Module.php","",$moduleInfo['path'])).'/controller/';
	$parts = array();
	if(is_dir($controllersPath)){
	    $dir = new \Core\Directory\Reader($controllersPath);
            
	    while($file = $dir->read()){
		if($file !== '.' && $file !== '..'){
		    $partName = str_replace("Controller.php","",$file);
		    $parts[] = array(
			'name'	    => strtolower($partName),
			'actions'   => $this->loadPartActions($partName)
		    );
		}
	    }
	}
	
	return $parts;
    }
    
    /**
     * Load actions of current part of curent module
     * @param string $partName The part name the name means controller
     */
    protected function loadPartActions($partName){
	$actualModuleDirParts = \Core\Directory\Reader::getActualInstance()->getDirParts();
        
        
	$actualModuleDir = $actualModuleDirParts[count($actualModuleDirParts)-2];
	$moduleName = str_replace("Module","",$actualModuleDir);
	$className = '\\'.ucfirst($moduleName).'\Controller\\'.$partName.'Controller';
        $methods = get_class_methods($className);
	$actions = array();
	if(!empty($methods))
	{
	    foreach($methods as $action){
		if(strpos($action, 'Action') !== false){
		    $actions[] = array('name' => strtolower(str_replace('Action','',$action)), 'method' => $action);
		}
	    }
	}
	
	return $actions;
    }
    
    
    
    /**
     * Cheks if adapter is allowed in PHP when not use session
     * @return string Name of allowed adapter
     */
    protected function getAdapterName(){
	//todo adapters, verification with PHP
	return 'Memory';
    }
    
    /**
     * @todo Implement model cache
     */
    protected function loadRoles(){
        $roles = \Model\Group::find();
        foreach($roles as $role){
            $this->roles[$role->id] = new Acl\Role($role->name);
        }
    }
    
    /**
     * @todo Implement model cache
     */
    
    protected function loadPermisions(){
        $permissions = \Model\Permissions::find(array('order'   => 'id ASC'));
        
        foreach($permissions as $permission){
                
                $this->permisions[strtolower($permission->module)][] = array(
                    'module'      => strtolower($permission->module),
                    'resource'    => strtolower($permission->resource),
                    'access'      => strtolower($permission->action),
                    'role'        => $permission->group_id
                );
        }
    }
    
    public function addComponent($componentName){
        $componentClass = '\Plugin\Authentication\Components\\'.ucfirst(strtolower($componentName));
        if(class_exists($componentClass)){
            
            $this->di->set(strtolower($componentName), function() use($componentClass){
                $component = new $componentClass();
            });
        }
    }
    
    
}
