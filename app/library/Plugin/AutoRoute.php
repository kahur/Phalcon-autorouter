<?php
namespace Plugin;


use Phalcon\Events\Event,
        Phalcon\Mvc\Dispatcher;

/**
 * AutoRoute class, depends ErrorController and 404Action in any module to follow non exist path's to error pages.
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky, Flipixo Ltd
 * @package name
 * @category name
 * @todo integrate support to work with Module component to improve APP performance
 * 
 */
class AutoRoute {
    
    /**
     * @var \Phalcon\Events\EventManager
     */
    protected $eventManger;
    /**
     * @var \Phalcon\Mvc\Application
     */
    protected $application;
    /**
     * @var \Phalcon\Mvc\Router
     */
    protected $router;
    /**
     * @var \Phalcon\DI
     */
    protected $di;
    /**
     * @var \Phalcon\Mvc\Dispatcher
     */
    protected $dispatcher;
    /**
     * @var String module name
     */
    protected $module;
    /**
     * @var array Actual module info, if exist
     */
    protected $moduleInfo;
    /**
     * @var String Actual controller name
     */
    protected $controller;
    /**
     * @var String Actual action name
     */
    protected $action;
    
    /**
     * @var array List of active modules
     */
    protected $modules = array();
    /**
     * @var array List of params
     */
    protected $params = array();
    /**
     * @var int|false false when the module was not found in url 
     */
    protected $urlModulePosition = 0;
    /**
     * @var int|false false when the controller was not found in url 
     */
    protected $urlControllerPosition = 1;
    /**
     * @var int|false false when the action was not found in url 
     */
    protected $urlActionPosition = 2;
    /**
     * @var \Core\Http\Request
     */
    protected $request;
    
    /**
     * @var boolean setup rest routing
     */
    protected $isRest = false;
    
    /**
     * @var array Rest routing default action settings
     */
    
    protected $restActions = array(
        'get'    => 'index',
        'post'   => 'create',
        'put'    => 'update',
        'delete' => 'delete'
    );
    
    /**
     * @var array|boolean enable modules for restrouting
     */
    protected $restModules = false;
    
    /**
     * @var string Variable will be filled when rest routing is enable 
     */
    protected $requestType;
    /**
     * @var decimal Phalconphp version
     */
    
    protected $version;
    
    public function __construct(\Phalcon\Events\Manager $manager) {
	$this->eventManger = $manager;
        $this->version = \Phalcon\Version::get();
	
    }
    
    /**
     * Predefined method for application event handler
     * the method will be called before load and start 
     */
    public function boot(Event $event,\Phalcon\Mvc\Application $application){
        $this->setPluginData($application);
        
        if($this->isRest){
            $this->requestType = strtolower($this->request->getMethod());
        }
	
	$this->registerNamespaces();
	//set dispatch parameters
	$this->setDispatchParams();	
	$this->registerServices();
	
	//reset request to catch cleaned params
	$this->request->removeMap();
	$this->request->clearItems();
	//parse url without module/c
	$this->request->parseUri();
	
        
	$this->di->set('request',$this->request);
	$this->router->setDefaultModule(ucfirst($this->module));
	$this->router->setDefaultController($this->controller);
        $this->router->setDefaultAction($this->action);
        
        if($this->di->getConfig()->application->autoObserver == 1){
            $this->registerObservers();
            $this->runObservers();
        }
        
    }
    
    protected function registerObservers(){    
        $config = $this->di->getConfig();
        if(isset($config->observser)){
            $moduleConfig = $config->observers;
            foreach($moduleConfig as $observer){
                if(strpos($observer, strtolower($this->module)) !== false){
                    \Core\Observer::registerObserver($observer);
                }
            }
        }
        
        
        //manual observers
//        //register onload observer
//        \Core\Observer::registerObserver(strtolower($this->module.'.'.$this->controller.'.onLoad'));
//        //register before load action observer
//        \Core\Observer::registerObserver(strtolower($this->module.'.'.$this->controller.'.before').ucfirst(strtolower($this->action)));
//        //register after load action observer
//        \Core\Observer::registerObserver(strtolower($this->module.'.'.$this->controller.'.after').ucfirst(strtolower($this->action)));   
//        //register on post in action observer
//        \Core\Observer::registerObserver(strtolower($this->module.'.'.$this->controller.'.').strtolower($this->action)).'post'; 
    }
    
    protected function runObservers(){
        $options = array(
            'module'    => $this->module,
            'resource'  => $this->controller,
            'action'    => $this->action
        );
        
        $di = $this->application->getDI();
        $this->eventManger->attach('dispatch', function($event) use($options,$di){
            $type = $event->getType();
            switch($type){
                case 'beforeDispatch':
                    \Core\Observer::runObserver(strtolower($options['module'].'.'.strtolower($options['resource']).'.').'onLoad');
                break;
                case 'beforeExecuteRoute':
                    \Core\Observer::runObserver(strtolower($options['module'].'.'.strtolower($options['resource']).'.before').ucfirst(strtolower($options['action'])));
                    $request = $di->getRequest();
                    if($request->isPost()){
                        
                        \Core\Observer::runObserver(strtolower($options['module'].'.'.$options['resource'].'.'.$options['action']).'.post');
                    }
                break;
                case 'afterExecuteRoute':
                    \Core\Observer::runObserver(strtolower($options['module'].'.'.$options['resource'].'.after').ucfirst(strtolower($options['action'])));
                break;
                default: break;
                    
            }
        });  
        
//        $request = $this->di->getService('request');
//        if($request->isPost()){
//            \Core\Observer::runObserver(strtolower($options['module'].'.'.$options['resource'].'.').strtolower($options['action']).'.post');
//        }
    }
    protected function registerServices(){
	$this->di->set('dispatcher', function(){
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            
	    $dispatcher->setDefaultNamespace(ucfirst($this->module).'\Controller');
		$dispatcher->setEventsManager($this->eventManger);
            return $dispatcher;
        });
	
    } 
    
    /**
     * Set main Class variables to correct plugin work
     * @param \Phalcon\Mvc\Application $application Full prepared application object     * 
     */
    protected function setPluginData(\Phalcon\Mvc\Application $application){
	$this->application = $application;
	$this->di = $this->application->getDI();
	$this->router = $this->di->get('router');
	$this->modules = $this->application->getModules();        
	
	$this->request = new \Core\Http\Request(new \Core\Http\Url\Map('/:module/:controller/:action'));
    }
    
    /**
     * Register namespaces to correct working class_exist function
     */
    protected function registerNamespaces(){
	$loader = new \Phalcon\Loader();
	$namespaces = array();
	foreach($this->modules as $moduleName => $module){
	    $namespaces[ucfirst($moduleName).'\Controller'] = '../app/modules/'.  ucfirst($moduleName).'Module/controller';
	    $namespaces[ucfirst($moduleName).'\Model'] = '../app/modules/'.ucfirst($moduleName).'Module/model';
	}
	
        $loader->registerNamespaces($namespaces);
        $loader->register();
    }

    
    /**
     * Set variables module,controller,action
     */
    protected function setDispatchParams(){
	if(!$this->module){
	    $this->setModule();
	}
	
	if(!$this->controller){
	    $this->setController();
	}
	
	if(!$this->action){
	    $this->setAction();
	}
    }
    
    /**
     * set object module variable
     */
    protected function setModule(){
	//check if module exist if yes prepare default or find module by first parameter in url
	if(!empty($this->modules)){
            
            if($this->version < 2){
                $this->module = $this->router->getDefaultModule();
            }
            else {
                $defaults = $this->router->getDefaults();
                $this->module = $defaults['module'];
            }
	    $module = $this->urlFormatToCamel($this->request->getParam('module'),true);
            
	    if($module && isset($this->modules[ucfirst($module)])){
                
		$this->moduleInfo = $this->modules[ucfirst($module)];
		$this->module = ucfirst($module);
		$this->request->removeParam('module');
	    }
	    else {
		$updateUrlMap = new \Core\Http\Url\Map('/:controller/:action');
		$this->request->setMap($updateUrlMap);
		$this->moduleInfo = $this->modules[ucfirst($this->module)];
	    }
	}
    }
    
    /**
     * @param String $controllerClassName Controller class
     * @return boolean true when class exists false when not
     */
    protected function isControllerExist($controllerClassName){	
//	class_exi
        
	return class_exists($controllerClassName);
    }
    
    /**
     * @param String $className Class name
     * @param String $actionName Full action name to check
     * @return boolean true when method in Object $className exists
     */
    protected function isActionExist($className,$actionName){
	return method_exists($className, $actionName);
    }
    
    /**
     * Set controller object varibale when:
     * 1. When url param founded by $urlControllerPosition and controller exist
     * 2. When controller from 1. doesnt exist, try to find default from configuration
     * 3. When 3. ( default ) controller doesnt exist set error controller 
     */
    protected function setController(){
	$controllerClass = null;
	$controller = $this->request->getParam('controller');
	//get controller from url	
	$controllerClass = '\\'.ucfirst($this->module).'\Controller\\'.$this->urlFormatToCamel($controller, true).'Controller';
        
	//if controller is not set in url or not exist
	if(!$this->isControllerExist($controllerClass))
	{
	    $urlMap = new \Core\Http\Url\Map('/:action');
	    $this->request->setMap($urlMap);
	    $controller = isset($this->moduleInfo['defaultController']) ? $this->moduleInfo['defaultController'] : null;
//	    echo $controller;
	    $controllerClass = '\\'.ucfirst($this->module).'\Controller\\'.$this->urlFormatToCamel($controller, true).'Controller';
	    
	    if(!$this->isControllerExist($controllerClass)){
		$controller = 'error';
	    }    
	}
	else {
	    $this->request->removeParam('controller');
	}
        
        
	
	$this->controller = strtolower($controller);
    }
    
    /**
     * Set controller object varibale when:
     * 1. When url param founded by $urlActionPosition and controller exist
     * 2. When action from 1. doesnt exist, try to find default from configuration
     * 3. When 3. ( default ) action doesnt exist set 404 action according to error controller
     * 4. When controller variable is set to error the variable will be set to 404 
     */
    protected function setAction(){
	$controllerClass = '\\'.ucfirst(strtolower($this->module)).'\Controller\\'.$this->urlFormatToCamel($this->controller, true).'Controller';
        //disable restrouting to not enabled modules
        if($this->isRest && ($this->restModules && !in_array(strtolower($this->module), $this->restModules))){
            $this->isRest = false;
        }
        
	if($this->isRest && strtolower($this->request->getMethod()) !== 'get'){
            $action = $this->restActions[$this->requestType];
            $actionName = $this->urlFormatToCamel($action).'Action';
        }
        else {
            $action = $this->urlFormatToCamel($this->request->getParam('action'));
            $actionName = $this->urlFormatToCamel($action).'Action';
        }
        
	if(!$this->isActionExist($controllerClass, $actionName)){
	    $urlMap = new \Core\Http\Url\Map('/');
	    $this->request->setMap($urlMap);
	    $action = isset($this->moduleInfo['defaultAction']) ? $this->moduleInfo['defaultAction'] : null;
	    $actionName = $this->urlFormatToCamel($action).'Action';
	    
	    if(!$action || !$this->isActionExist($controllerClass, $actionName) || $this->controller == 'error'){
		$action = 'index';
		if($this->controller === 'error' || 
			(isset($this->moduleInfo['defaultAction']) && $this->moduleInfo['defaultAction'] === $action) )
		{
		    $action = 'error404';
		    $this->controller = 'error';
		}
	    }
	}
	else {
            /** @todo check if commented works in all situations */
            //if(!$this->isRest){
                $this->request->removeParam('action');
            //}
            
	}
	
	$this->action = $action;
    }
    
    /**
     * Convert url format to camel case format eg.: my-action will be replaced for myAction
     * @param string $string String part of url
     */
    protected function urlFormatToCamel($string,$firstCamel = false){	
	if(strpos($string, '-') !== false && $string !== null){
	    $tmpString = '';
	    $stringParts = explode("-",$string);
	    foreach($stringParts as $key => $part){
		if($key === 0){
		    $tmpString .= ($firstCamel === true) ? ucfirst(strtolower($part)) : strtolower($part);
		}
		else {
		    $tmpString .= ucfirst(strtolower($part));
		}
	    }
	    
	    return $tmpString;
	}
	
	return ($firstCamel) ? ucfirst(strtolower($string)) : strtolower($string);
    }
    
    /**
     * Set default rest action for get request
     * @param string $name Action name excluded "Action"
     */
    public function setGetAction($name){
        $this->restActions['get'] = $name;
    }
    
    /**
     * Set default rest action for post request
     * @param string $name Action name excluded "Action"
     */
    public function setPostAction($name){
        $this->restActions['post'] = $name;
    }
    
    /**
     * Set default rest action for put request
     * @param string $name Action name excluded "Action"
     */
    public function setPutAction($name){
        $this->restActions['put'] = $name;
    }
    
    /**
     * Set default rest action for delete request
     * @param string $name Action name excluded "Action"
     */
    public function setDeleteAction($name){
        $this->restActions['delete'] = $name;
    }
    
    /**
     * Enable restrouting functions
     * @param array $modules enable rest routing for specific modules
     */
    public function enableRestRouting(array $modules = null){
        if($modules){
            $this->restModules = $modules;
        }
        $this->isRest = true;
    }
    
    /**
     * Disable restrouting
     */
    public function disableRestRouting(){
        $this->isRest = false;
    }
    
}

