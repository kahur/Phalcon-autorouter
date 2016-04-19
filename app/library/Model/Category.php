<?php
namespace Model;

/**
 * Description of Category
 *
 * @author Flipixo
 */
class Category extends \Core\Model {
    //put your code here
    public $id;
    public $parent_id;
    public $name;
    public $hash;
    public $url;
    
    public function getCount($cache = true){
        if($cache){
            $cache = $this->getDI()->getCache();
            if($cache){
                if($cache->exists('category_count_'.$this->id)){
                    
                    return $cache->get('category_count_'.$this->id);
                }
            }
        }
        
        $count = Discount\Category::count(array(
            'category_id = :category:',
            'bind' => array(
                'category' => $this->id
            )
        ));
        if($cache){
            if(!$cache->exists('keys')){
                $keys = array();
            }
            $keys = $cache->get('keys');
            $cache->save('category_count_'.$this->id,$count);
            
            if(!in_array('category_count_'.$this->id, $keys)){
                $keys[] = 'category_count_'.$this->id;
                $cache->save('keys', $keys);
            }
        }
        
        return $count;
    }
    
    public function getChilds(){
        return self::find('parent_id = '.$this->id);
    }
    
}
