<?php
namespace Model;

/**
 * Description of Group
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Page extends \Core\Model {
    public $id;
    public $parent_id;
    public $layout;
    public $title;
    public $url;
    public $content;
    public $is_pubic;
    public $created_at;
    public $updated_at;
    public $category;
    public $subcategory;
    public $forward;
    public $sort_id;
    public $is_footer;
    
    private $crumbs;
    
    public function initialize(){
        
        if(!$this->id){
            $this->created_at = date("Y-m-d H:i:s",time());
        }
        else {
            $this->updated_at = date("Y-m-d H:i:s",time());
        }
    }
    
    protected function loadTree($pageId = null){
        $item = self::findFirst($pageId);
        $item->items = array();
        if(!$item){
            return null;
        }
        
        $subItems = self::find(array(
            'parent_id = :parent:',
            'bind' => array('parent' => $pageId) 
        ));
        
        if(!$subItems){
            return $tree;
        }
        
        
        foreach($subItems as $items){
            $items->items = array();
            $items->items = $this->loadTree($items->id);
//            $item->items[] = $items;
        }
        
        return $item;
        
    }
    
    protected function loadBreadCrumbs($pageId = null){
        
        $curPage = self::findFirst($pageId);
        if($curPage){
            $this->crumbs['/manager/structure/'.$curPage->id.'/'] = $curPage->title;
        }
        
        $page = self::findFirst(array('parent_id = :parent:','bind' => array('parent' => $page->parent_id)));
        if($page){
            $this->loadBreadCrumbs($page->parent_id);
        }
        else {
            $this->crumbs['/manager/structure/'.$page->id.'/'] = $page->title;
        }
    }
    
    public function getBreadCrumbs(){
        $this->loadBreadCrumbs($this->id);
        
        return $this->crumbs;
    }
    
    public function getTree(){
        return $this->loadTree($this->id);
    }
    
    public function getWidgets(){
        return array();
    }
    
    public function loadFullTree($page = 0){
        $mainItems = self::find(array(
            'parent_id = :parent: AND is_public = 1',
            'bind' => array(
                'parent' => intval($page)
            ),
            'order' => 'sort_id ASC'
        ));
        
        $menu = array();
        foreach($mainItems as $mainItem){
            $item = $mainItem->toArray();
            $item['items'] = $this->loadFullTree($mainItem->id);
            
            
            
            $menu[] = $item;  
        }
        
        return $menu;
    }
}

