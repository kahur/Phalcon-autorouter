<?php namespace Helper;


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
		
                $lowerCaseModuleName = $module->moduleName;
		
                $applicationConfig[$lowerCaseModuleName] = array(
                    'className'	    => '\\'.((isset($module->moduleClass)) ? $module->moduleClass : ucfirst(strtolower($module->moduleName)).'\Module'),
                    'path'		    => (isset($module->moduleClassPath)) ? $module->moduleClassPath : '../app/modules/'.ucfirst(strtolower($module->moduleName)).'Module/Module.php',
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
        $directory = new \Core\Directory\Manager('../app/modules/'.ucfirst(strtolower($module)).'Module/config/');
        $cache = \Core\Cache::factory('Data', 'Memcache');
        $mainConfig = $this->di->getConfig();
        while($file = $directory->read()){
            if($file !== '.' && $file !== '..'){
                $pathToConfig = $directory->getPath().$file;                
                if(!$cache->exists(\Core\String::webalize($pathToConfig)))
                {
                    if(file_exists($pathToConfig)){
                         $config = new \Phalcon\Config\Adapter\Json($pathToConfig);
                         $cache->save(\Core\String::webalize($pathToConfig),$config);
                    }
                }
                
                $mainConfig->merge($cache->get(\Core\String::webalize($pathToConfig)));
            }
        }
        
        //observers
        if($mainConfig->offsetExists('observers')){
            $this->registerObservers($mainConfig->observers);
        }
        
    }
    
    protected function registerObservers(\Phalcon\Config $observers){
        foreach($observers as $server){
            \Core\Observer::registerObserver($server);
        }
    }

}