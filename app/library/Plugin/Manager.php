<?php
namespace Plugin;

/**
 * Description of Manager
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Manager extends \Phalcon\Mvc\User\Component {
    //put your code here
    protected $path;
    /**
     * List of all plugins
     */
    protected $pluginList = array();
    
    private $pluginMap = array();
    public function __construct(){
        $this->path = dirname(__FILE__);
        $this->loadPlugins();
    }
    
    protected function loadPlugins(){
        $directory = new \Core\Directory\Manager($this->path);
        while($dir = $directory->read()){
            
            if(is_dir($directory->getPath().$dir) && $dir !== '.' && $dir !== '..'){
                $pluginDir = $directory->getPath().$dir.'/';
                $plugins = \Model\Plugins::findFirst(array(
                    'path = :path:',
                    'bind' => array(
                        'path' => $pluginDir
                    )
                ));
                if($plugins && file_exists($pluginDir.'Init.php') && file_exists($pluginDir.'config/config.json') && !file_exists($pluginDir.'install/')){
                    
                    $state = 'Installed';
                    $info = array(
                        'name'  => $dir,
                        'state' => $state
                    );
                    
                    $pluginClass = "\Plugin\\".$dir."\Install\Install";
                    $version = $pluginClass::version();
                    
                    if($version > $plugins->version){
                        $info['upgrade'] = $version;
                    }
                    
                    $this->pluginList[] = $info;
                    
                    $this->pluginMap[$dir] = count($this->pluginList)-1;
                }
                else if(!$plugins && file_exists($pluginDir.'Install/')){
                    $this->pluginList[] = array(
                        'name'  => $dir,
                        'state' => 'Install'
                    );
                    
                    $this->pluginMap[$dir] = count($this->pluginList)-1;
                }
                
            }
        }
    }
    
    public function getPlugins(){
        return $this->pluginList;
    }
    
    public function initInstall($pluginName,  \Phalcon\Mvc\View $view){
        if(!isset($this->pluginMap[$pluginName])){
            return false;
        }
        $db = $this->di->getDb();
        $className = '\Plugin\\'.$pluginName.'\Install\Install';
        $install = new $className($db, $this->view, $this->di);
        $install->setup();
        return true;
    }
    
    public function uninstall($pluginName){
        if(!isset($this->pluginMap[$pluginName])){
            return false;
        }
        $db = $this->di->getDb();
        $className = '\Plugin\\'.$pluginName.'\Install\Install';
        $install = new $className($db, $this->view, $this->di);
        $install->uninstall();
        return true;
    }
}
