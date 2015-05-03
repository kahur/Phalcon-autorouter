<?php
namespace Softdream;
/**
 * Description of BaseModule
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
abstract class BaseModule implements \Phalcon\Mvc\ModuleDefinitionInterface {
    
    protected $moduleName;
    
    public function __construct($namespace) {
	if(!$namespace){
	    throw new \Phalcon\Exception('Namespace must be defined');
	}
	$this->moduleName = ucfirst(strtolower($namespace));
    }
    
    /**
     * Registers an autoloader related to the module
     *
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function registerAutoloaders(\Phalcon\DiInterface $di = null)
    {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(
            array(
                $this->moduleName.'\Controller'  => '../app/'.$this->moduleName.'Module/controller',
                $this->moduleName.'\Model' => '../app/'.$this->moduleName.'Module/model'
            )
        );
        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param \Phalcon\DiInterface $di
     */
    public function registerServices(\Phalcon\DiInterface $di = null)
    {
        $di->set('view', function(){
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir('../app/'.$this->moduleName.'Module/views/');
            $view->registerEngines(
                array(
                    '.phtml' => 'templateEngine'
                )
            );
            return $view;
        });
    }
}