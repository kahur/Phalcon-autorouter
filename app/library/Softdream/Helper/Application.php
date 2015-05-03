<?php 
namespace Softdream\Helper;

use Phalcon\Exception;

class Application {
    /**
     * @var \Phalcon\DI
     */
    protected $di;
    /**
     * @var \Phalcon\Mvc\Application
     */
    protected $application;
    /**
     * @var \Config;
     */
    protected $config;

    /**
     * @param \Phalcon\Mvc\Application Application instance
     * @throws \Phalcon\Exception
     * @internal param \Phalcon\DI $di Dependency injection container
     */
    public function __construct(\Phalcon\Mvc\Application $application) {
        $this->application = $application;

        try {
            $this->di = $this->application->getDI();
            $this->config = $this->di->get('config');
        }
        catch(Exception $e){
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    public function registerModules(){
        try {
            $modules = $this->config->modules;
            $applicationConfig = array();
            
	    
	    
	    $defaultModule = false;
	    
            foreach($modules as $module){
		
                $lowerCaseModuleName = strtolower($module->moduleName);
		
                $applicationConfig[ucfirst($module->moduleName)] = array(
                    'className'	    => '\\'.((isset($module->moduleClass)) ? $module->moduleClass : ucfirst($module->moduleName).'\Module'),
                    'path'		    => (isset($module->moduleClassPath)) ? $module->moduleClassPath : '../app/modules/'.ucfirst($module->moduleName).'Module/Module.php',
		    'baseUrl'	    => (isset($module->baseUrl)) ? $module->baseUrl : null,
		    'defaultController' => (isset($module->defaultController)) ? $module->defaultController : null,
		    'defaultAction' => (isset($module->defaultAction)) ? $module->defaultAction : null
                );
                
                $this->loadModuleConfiguration($module->moduleName);
		
		if(isset($module->defaultModule) && $module->defaultModule == 1 && !$defaultModule){
		    $defaultModule = $lowerCaseModuleName;
		}
		
            }
            
            
	    $router = new \Phalcon\Mvc\Router(false);
	    //set default module
	    if(!$defaultModule){
		throw new Exception('Default module is not set');		
	    }
	    
	    $router->setDefaultModule($defaultModule);
	    //register route to di
            $this->di->set('router', function() use($router){
                return $router;
            });
            $this->application->registerModules($applicationConfig);
        }
        catch(Exception $e){
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }
    
    protected function loadModuleConfiguration($module){
        $pathToConfig = '../app/modules/'.ucfirst($module).'Module/config/config.json';
        $cache = \Core\Cache::factory('Data', 'Memcache');
        $mainConfig = $this->di->getConfig();
//        if(!$cache->exists('config'))
//        {
        if(file_exists($pathToConfig)){
             $config = new \Phalcon\Config\Adapter\Json($pathToConfig);
             $mainConfig->merge($config);
        }
//        }
        
    }

}