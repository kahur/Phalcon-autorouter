<?php
namespace Plugin\Structure\Install;

/**
 * Description of Install
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Install extends \Core\Install {
    public $pluginName = 'Structure';
    protected function getCompatibility() {
        return 1;
    }

    protected function getConfigurationFields() {
        $fields = array();
        
        return $fields;
    }
    
    protected function getMenuConfig(){
        return array(
            'Struktura webu' => array(
                array(
                    'module'    => 'manager',
                    'resource'  => 'structure',
                    'access'    => 'index',
                    "css-class" => "pe-7f-note",
                    'url'       => 'manager/structure'
                ),
                array(
                    "Seznam stránek" => array(
                        'module'    => 'manager',
                        'resource'  => 'structure',
                        'access'    => 'index',
                        'url'       => 'manager/structure'
                    ),
                    "Vytvořit stránku" => array(
                        'module'    => 'manager',
                        'resource'  => 'structure',
                        'access'    => 'add',
                        'url'       => 'manager/structure/add'
                    )
                )
            )
            
        );
    }

    protected function getName() {
        return 'Structure';
    }

    protected function getObserverCallbacks() {
        return array(
            'manager.structure.add.post' => array(
                'class'     => '\Plugin\Structure\Router',
                'method'    => 'dispatch',
                'args'      => 'structure:add'
            ),
            'manager.structure.beforeAddmodule' => array(
                'class'     => '\Plugin\Structure\Router',
                'method'    => 'dispatch',
                'args'      => 'structure:addPageForm'
            ),
            'manager.structure.beforeAddcontent' => array(
                'class'     => '\Plugin\Structure\Router',
                'method'    => 'dispatch',
                'args'      => 'structure:addPageForm'
            )   
        );
    }

    protected function getObservers() {
        return array(
            'plugin.panelManagment.init',
            'plugin.panelManagment.add',
            'plugin.panelManagment.edit',
            'plugin.panelManagment.delete',
            'plugin.panelManagment.post'
        );
    }
    
    public static function version(){
        return 1.4;
    }
    protected function getVersion() {
        return self::version();
    }
}
