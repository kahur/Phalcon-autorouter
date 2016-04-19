<?php
namespace Plugin;

/**
 * Description of Broker
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Loader {
    //put your code here
    /**
     * @var \Phalcon\Mvc\Application
     */
    private $application;
    
    /**
     * @var \Phalcon\DI
     */
    private $di;
    
    /**
     * @var \Phalcon\Config
     */
    private $config;
    
    /**
     * @var String path
     */
    private $path;
    
    /**
     * @var array initialized plugins
     */
    private $plugins = array();
    public function __construct(\Phalcon\Mvc\Application $application, \Phalcon\DI $di, \Phalcon\Config $config) {
       
        $this->application = $application;
        $this->di = $di;
        $this->config = $config;
    }
    
    /**
     * Set plugins directory
     * @param String path to plugins directory
     */
    
    public function setPluginDirectory($dir){
        if(!is_dir($dir)){
            throw new \Plugin\Loader\Exception('Directory '.$dir.' does not exists.', 500);
        }
        
        $this->path = $dir;
    }
    
    protected function isPlugin($plugin,$path){
        //first we check if the plugin has configuration file
        $pathToConfig = $path.$plugin.'/config/config.json';
        if(is_file($pathToConfig)){
            $config = new \Phalcon\Config\Adapter\Json($pathToConfig);
            if($config->offsetExists('plugin')){
                if($config->plugin->$plugin->offsetGet('compatibility') <= $this->config->system->version){
                    $this->config->merge($config);
                    return true;
                }
            }                
        }        
        return false;
    }
    
    protected function initializePlugin($config){
        $initClass = $config->initClass;
        
        return new $initClass($config, $this->application, $this->di);
    }
    
    public function boot(\Phalcon\Events\Event $event,\Phalcon\Mvc\Application $application){
        if($this->path){
            $directory = new \Core\Directory\Manager($this->path);
            while($dir = $directory->read()){
                if($dir !== '.' && $dir !== '..'){
                    //find plugin configuration
                    if(is_dir($directory->getPath().$dir)){
                        if($this->isPlugin($dir,$directory->getPath())){
                            $this->plugins[$dir] = $this->initializePlugin($this->config->plugin->$dir);
                        }
                    }
                }            
            }
        }
    }
}
