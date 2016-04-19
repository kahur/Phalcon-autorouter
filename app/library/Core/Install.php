<?php
namespace Core;

/**
 * Description of Install
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
abstract class Install {
    
    /**
     * @var \Phalcon\Db\Adapter\Pdo\Mysql
     */
    private $db;
    
    /**
     * @var \Phalcon\DI
     */
    private $di;
    
    /**
     * @var \Phalcon\Mvc\View
     */
    private $view;
    
    /**
     * Error list
     */
    private $errors = array();
    
    /**
     * Final constructor to prevent access to db and system services
     */
    final public function __construct(\Phalcon\Db\Adapter\Pdo\Mysql $db, \Phalcon\Mvc\View $view, $di) {
        $this->db = $db;
        $this->di = $di;
        $this->view = $view;
    }
    /**
     * Get current version of plugin
     * @return double
     */
    abstract protected function getVersion();
    
    /**
     * Get compatibility with system versions
     * @return int
     */
    abstract protected function getCompatibility();
    
    /**
     * Plugin name
     * @return String
     */
    abstract protected function getName();
    
    /**
     * Observers
     * @return array
     */
    abstract protected function getObservers();
    
    /**
     * Get callbacks to system, or plugins registerd observers
     * @return array
     */
    
    abstract protected function getObserverCallbacks();
    
    /**
     * Return configuration fields, which will be shown on install page to install it into your configuration file
     * @return array
     */
    abstract protected function getConfigurationFields();
    
    private function getSQL(){
        $reflection =  new \ReflectionClass(get_class($this));
        
        $installSQL = dirname($reflection->getFileName()).'/sql/';
        $directory = new \Core\Directory\Manager($installSQL);
        $sql = '';
        while($sqlFile = $directory->read()){
            if($sqlFile !== '.' && $sqlFile !== '..' && is_file($directory->getPath().$sqlFile)){
                $file = $directory->getPath().$sqlFile;
                $stream = fopen($file,'r');
                $stream = fread($stream,filesize($file));
                $sql .= $stream."\n";
            }
        }     
        
        return $sql;
    }
    
    final public function setup(){
        $request = $this->di->getRequest();
        
        if($request->isPost()){
            if(!$this->install($request)){
                $this->uninstall();
                $this->di->getFlash()->warning('Instalace se nezdařila.');
            }
            else {
                if(isset($this->view->isUpgrade)){
                    $this->di->getFlash()->success('Upgrade proběhl úspěšně');
                }
                else {
                    $this->di->getFlash()->success('Instalace proběhla úspěšně');
                }
                $this->di->getResponse()->redirect('/manager/plugins/');
            }
        }
        
        $fields = $this->getConfigurationFields();
        
        $form = new Plugin\Setup\Form();       
        $form->customFields($fields);
        
        //prepare sql info
        $this->view->setup = $form;
        $this->view->name = $this->pluginName;
        $this->view->fields = $fields;
    }    
    
    final public function uninstall(){
        $name = $this->getName();
        $plugins = \Model\Plugins::findFirst(array(
            'name = :name:',
            'bind' => array('name' => $name)
        ));
        
        if($plugins){
            $plugins->delete();
        }
        
        $reflection =  new \ReflectionClass(get_class($this));   
        $path = dirname($reflection->getFileName());
        $path = str_replace("/Install","",$path).'/';
        //remove config file
        @unlink($path.'config/config.json');
        @unlink($path.'Init.php');
    }
    
    private function install(Http\Request $request){
        //first we try to install db
        if(!$this->installDB()){
            return false;
        }
        
        if($this->installInitScript()){
            if($this->installConfiguration()){
                $reflection =  new \ReflectionClass(get_class($this)); 
                $path = dirname($reflection->getFileName());
                $path = str_replace("/Install","",$path).'/';
                
                $plugins = \Model\Plugins::findFirst(array(
                    'path = :path:',
                    'bind' => array('path' => $path)
                ));
                
                if(!$plugins){
                    $plugins = new \Model\Plugins();
                }
                
                $plugins->name = $this->getName();
                $plugins->version = $this->getVersion();
                $plugins->path = $path;
                $plugins->created_at = date("Y-m-d H:i:s",time());
                return $plugins->save();
            }
            
            return false;
        }
        
        return false;        
    }
    
    private function installDB(){
        $valid = true;
        
        $sql = $this->getSQL(); 
//        echo $sql;
//        exit;
        try {
            $this->db->begin();
            $this->db->execute($sql);
            $this->db->commit();
        }
        catch(\Exception $e){
            if($valid){
                $this->db->rollback();
            }
            
            $valid = false;
            $this->errors[] = $e->getMessage();
            var_dump($e->getMessage());
            exit;
        }
        
        return $valid;
        
    }
    
    private function installInitScript(){
        $name = ucfirst(strtolower($this->pluginName));
        $observerString = '';
        foreach($this->getObservers() as $observer){
            $observerString .= "\Core\Observer::registerObserver('".$observer."');\n";                    
        }
        
        $callbacksString = '';
        foreach($this->getObserverCallbacks() as $observer => $callback){
            $callbacksString .= "\Core\Observer::registerListener('".$observer."',function(){
                \$object = new ".$callback['class']."('".$callback['args']."');
                \$object->".$callback['method']."();
            });\n";
        }
        
        //everything is fine now let's build initScript
        
$classString = "<?php
            
namespace Plugin\\$name;

class Init {
    /**
      * @var \Phalcon\DI 
      */
     private \$di;

    /**
      * @var \Phalcon\Mvc\Application
      */
     private \$app;

    /**
      * @var \Phalcon\Config plugin configuration
      */
     private \$config;

    /**
      * @var \Core\Http\Request
      */
     private \$request;

     public function __construct(\Phalcon\Config \$config, \Phalcon\Mvc\Application \$app, \Phalcon\DI \$di){
        \$this->config = \$config;
        \$this->app = \$app;
        \$this->di = \$di;
        \$this->request = \$di->getService('request');
        \$this->registerObservers();
        \$this->registerObserverCallbacks();  
    }

    public function registerObservers(){
        ".$observerString."
    }
    
    public function registerObserverCallbacks(){
        ".$callbacksString."
    }

}";
        $reflection =  new \ReflectionClass(get_class($this));        
        $path = str_replace('/Install','',dirname($reflection->getFileName()));
       
        $directory = new Directory\Manager($path);
        if(!$directory->isWritable()){
            throw new \Exception('Instalation failed due to plugin directory ('.$directory->getPath().') is not writable.');
        }
        
        $fopen = fopen($directory->getPath().'Init.php','w');
        if(!fwrite($fopen, trim($classString))){
            throw new \Exception('Cannot write init script');
        }
        
        return true;
    }
    
    private function installConfiguration(){
        $config = $this->di->getRequest()->getPost();
        $config['compatibility'] = $this->getCompatibility();
        $config['initClass'] = '\Plugin\\'.ucfirst($this->getName()).'\Init';
        $config['build'] = $this->getVersion();
        
        $cfg = array(
            'menu' => $this->getMenuConfig(),
            'plugin' => array(
                ucfirst($this->getName()) => $config
            ),
            'observers' => $this->getObservers()
        );
        
        $configString = json_encode($cfg);
        
        $reflection =  new \ReflectionClass(get_class($this));        
        $path = dirname($reflection->getFileName());
        $path = str_replace("/Install","",$path).'/';
        $directory = new \Core\Directory\Manager($path.'config/',false);
        if(!$directory->isExist()){
            $directory = new \Core\Directory\Manager($path);
            $directory = $directory->createDirectory('config', 0775);
        }
        
        if(!$directory->isWritable()){
            throw new Exception('Instalation failed due to plugin config directory ('.$directory->getPath().') is not writable.');
        }
        
        $fopen = fopen($directory->getPath().'config.json','w');
        if(!fwrite($fopen, $configString)){
            throw new Exception('Cannot write configuration file script');
        }
        
        return true;
    }
}
