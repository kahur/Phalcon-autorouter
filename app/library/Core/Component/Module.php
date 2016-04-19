<?php
namespace Core\Component;

/**
 * Description of Modules
 *
 * @author softdream
 */
class Module extends \Phalcon\Mvc\User\Component {
    /**
     * @var \Phalcon\Config
     */
    protected $config;
    
    /**
     * @var \Phalcon\DI
     */
    protected $di;
    
    /**
     * @var array List of module info
     */
    protected $modules;
    
    /**
     * @var \Phalcon\Mvc\Router
     */
    protected $router;
    
    
    
    protected $actualModule;
    protected $actualResource;
    protected $actualAction;
    
    public function __construct() {
        $this->di = $this->getDI();
        $this->config = $this->di->getConfig();
        $this->router = $this->di->getRouter();
        
        $this->actualModule = $this->router->getModuleName();
        $this->actualResource = $this->router->getControllerName();
        $this->actualAction = $this->router->getActionName();
        //load modules info
        $this->load();
    }
    
    /**
     * @return array Return all loaded info about modules
     */
    public function getModules(){
        return $this->modules;
    }
    
    /**
     * Returns module info
     * @param string $module Get module info, when module is set to null it will reutrn actual module info
     * @return array Description
     */
    public function getModuleInfo($module = null){
         
        if(!$module){
            return $this->modules[strtolower($this->actualModule)];
        }
        
        return isset($this->modules[$module]) ? $this->modules[$module] : array();
    }
    
    /**
     * Returns resource info
     * @param string $resource Get module info, when resource is set to null it will reutrn actual module and resource info
     * @return array Description
     */
    public function getResourceInfo($resource = null){
        $moduleInfo = $this->getModuleInfo();
        
        if(!$resource){
            return $moduleInfo['resources'][$this->actualResource];
        }
        
        return isset($moduleInfo['resources'][$resource]) ? $moduleInfo['resources'][$resource] : array();
    }
    
    /**
     * Returns module info
     * @param string $action Get module info, when module is set to null it will reutrn actual module, resource and action info
     * @return array Description
     */
    public function getActionInfo($action = null){
        $resourceInfo = $this->getResourceInfo();
        
        if(!$action){
            return $resourceInfo['actions'][$this->actualResource];
        }
        
        return isset($resourceInfo['actions'][$action]) ? $resourceInfo['actions'][$action] : array();
    }
    
    /**
     * Load main information about module and module resources and resources actions
     * @todo Cache results for prod.
     */
    protected function load(){
        if(!isset($this->config->modules)){
            new \Phalcon\Mvc\User\Component\Exception("Missing modules configuration. No modules to load.");
        }
        
        foreach($this->config->modules as $module){
            $modulePath = '../app/modules/'.ucfirst(strtolower($module->moduleName)).'Module';
            $moduleInfo = array(
                'name'              => $module->moduleName,
                'baseUrl'           => $module->baseUrl,
                'defaultResource'   => $module->defaultController,
                'defaultAction'     => $module->defaultAction,
                'isDefault'         => isset($module->defaultModule) ? true : false,
                'path'              => $modulePath,
                'initClass'         => '\\'.ucfirst(strtolower($module->moduleName)).'\Module',
                'initClassPath'     => $modulePath.'/Module.php',
                'resources'         => array()
            );
            
            $moduleInfo['resources'] = $this->getResources($moduleInfo);
            
            $this->modules[strtolower($module->moduleName)] = $moduleInfo;
        }
    }
    
    /**
     * Load module resources
     */
    protected function getResources(array $moduleInfo){
        
        $controllers = $moduleInfo['path'].'/controller/';
        $resourceInfo = array();
        
        if(is_dir($controllers)){
            $dir = new \Core\Directory\Reader($controllers);
            while($controller = $dir->read()){
                if($controller !== '.' && $controller !== '..'){
                    $resourceName = str_replace("Controller.php","",$controller);
                    $resourcePath = $controllers.$controller;
                    $resourceUrl = $moduleInfo['baseUrl'];
                    
                    if($resourceUrl === '/'){
                        $resourceUrl = str_replace('/', "", $resourceUrl);
                    }
                    
                    if($resourceName !== 'Index'){
                         $resourceUrl .= '/'. \Core\String::camelFormatToUrl(lcfirst($resourceName));
                    }
                    $resourceInfo[strtolower($resourceName)] = array(
                        'name'      => strtolower($resourceName),
                        'path'      => $resourcePath,
                        'url'       => $resourceUrl.'/',
                        'class' => '\\'.$moduleInfo['name'].'\Controller\\'.$resourceName.'Controller',
                        'actions'   => array()
                    );
                    
                    
                    $resourceInfo[strtolower($resourceName)]['actions'] = $this->getActions($resourceInfo[strtolower($resourceName)]);
                    
                    
                }
            }
        }
        
        return $resourceInfo;
    }
    
    /**
     * Load resource actions
     */
    protected function getActions(array $resourceInfo){
        $actions = get_class_methods($resourceInfo['class']);
        $actionInfo = array();
        if(!empty($actions))
        {
            foreach($actions as $action){
                if(strpos($action, 'Action') !== false){
                    $actionName = str_replace('Action', "", $action);
                    $actionUrl = $resourceInfo['url'];
                    
                    if(strtolower($actionName) !== 'index')
                    {
                        $actionUrl .= \Core\String::camelFormatToUrl(lcfirst($actionName)).'/';
                    }
                    
                    $actionInfo[strtolower($actionName)] = array(
                        'name'  => strtolower($actionName),
                        'url'   => $actionUrl
                    );
                    
                    $resourceInfo['actions'][$actionName] = $actionInfo;
                }
            }
        }
        
        return $actionInfo;
    }
}
