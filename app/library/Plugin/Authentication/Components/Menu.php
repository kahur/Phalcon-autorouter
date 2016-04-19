<?php
namespace Plugin\Authentication\Components;

/**
 * Description of Menu
 * @author flipixo
 */
class Menu extends \Phalcon\Mvc\User\Component {
    /**
     * @var \Plugin\Authentication\AccessControl
     */
    protected $acesscontrol;
    /**
     * @var \Acl
     */
    protected $acl;
    
    /**
     * @var \Phalcon\DI
     */
    protected $di;
    
    /**
     * Authentication object
     * @var \Core\Auth
     */
    protected $auth;
    
    /**
     * Menu configuration from modules
     * @var array
     */
    protected $config;
    
    /**
     * Generated menu
     * @var array
     */
    
    protected $menuItems;
    
    /**
     * Filtered menu by Acl
     * @var array
     */
    protected $menu;
    
    public function __construct($menu){
        $this->menuItems = $menu;
        //check is user is logged in 
        $this->auth = \Core\Auth::getIdentity();
        if($this->auth)
        {
            $this->loadData();
        }      
    }
    
    /**
     * Load Menu component data
     */
    protected function loadData(){
        //load \Phalcon\DI container
        $this->di = $this->getDI();
        
        //load ACL object with defined rights
        $this->acl = $this->di->getAcl();
              
        //build menu items
        
        $this->buildMenu(); 
            
        
        
    }   
    
    
    
    /**
     * Build menu with ACl rights
     */
    protected function buildMenu(){
        $menuItems = (array) $this->menuItems;
        
        $module = strtolower($this->getDI()->getRouter()->getModuleName());
        $controller = strtolower($this->dispatcher->getControllerName());
        $action = strtolower($this->dispatcher->getActionName());
        foreach($menuItems as $sectionName => $value){
            $value = (array) $value;
            foreach($value as $section => $menuItem)
            {
                //menu items from menu module configuration
                $item = (array) $menuItem;
                
                //build top menu items
                if($section == 0 && (isset($item['module']))){
                    $access = $this->checkItemRights($item['module'], $item['resource'], $item['access']);
                    
                    if($access === true)
                    {
//                        echo "test";
                        $this->menu[$item['module']][$sectionName] = array(
                            'name'      => $sectionName,
                            'url'       => $item['url'],
                            'css'       => isset($item['css-class']) ? $item['css-class'] : 'fa fa-th-large',
                            'active'    => (strtolower($module) === strtolower($item['module']) && strtolower($controller) === strtolower($item['resource'])) ? 1 : 0,
                            'items'     => null
                        );
                    }
                    
                }
                else {
                    //build sub menu when top item is set
                    if(isset($item['module']) && isset($this->menu[$item['module']]))
                    {
                        $access = $this->checkItemRights($item['module'], $item['resource'], $item['access']);
                        if($access === true)
                        { 
                            $isActive = ($module === strtolower($subItem['module']) && $controller === strtolower($subItem['resource']) && $action === strtolower($subItem['access'])) ? 1 : 0;
                             $this->menu[$item['module']][$sectionName] = array(
                                  'name'      => $sectionName,
                                  'url'       => $item['url'],
                                  'active'      => $isActive
                              );
                             
                             
                        }
                    }
                    else if(!isset($item['module'])) { //build sub menu when top item is not set
                        
                        $subItems = (array) $item;
                            foreach($subItems as $name => $subItem){
                              $access = $this->checkItemRights($subItem['module'], $subItem['resource'], $subItem['access']);  
                              if(isset($this->menu[$subItem['module']]) && $access === true)
                              {
                                  $isActive = ($module === strtolower($subItem['module']) && $controller === strtolower($subItem['resource']) && $action === strtolower($subItem['access'])) ? 1 : 0;
                                  if($isActive){
                                      $this->menu[$subItem['module']][$sectionName]['active'] = 1;
                                  }
                                  $this->menu[$subItem['module']][$sectionName]['items'][] = array(
                                      'name'        => $name,
                                      'url'         => $subItem['url'],
                                      'active'      => $isActive
                                  );
                              }
                        }
                    }                   
                    
                }
                
            }
            
            
        }
    }
    
    protected function checkItemRights($module,$controller,$action){
        $acl = $this->acl->getAcl(ucfirst($module));
        if(!is_array($acl)){
            $roleName = $this->auth->role;
            $controller = str_replace('-', '', $controller);
            $action = str_replace('-','',$action);
            if(!$roleName){
                return false;
            }
            
            $access = $acl->isAllowed($roleName, strtolower($controller), strtolower($action));
            
            return $access;
        }
        
    }
    
    public function getMenu(){
        return $this->menu;
    }
    
    public function getModuleMenu(){
        return $this->menu[strtolower($this->router->getModuleName())];
    }
    
    
}
