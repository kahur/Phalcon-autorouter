<?php


namespace Model;

/**
 * Description of Discount
 *
 * @author Flipixo
 */
class Discounts extends \Core\Model {
    //put your code here
    public $id;
    public $city_id;
    public $item_id;
    public $title;
    public $image;
    public $start;
    public $end;
    public $voucher_start;
    public $voucher_end;
    public $customers;
    public $min_customers;
    public $max_customers;
    public $price;
    public $original_price;
    public $discount;
    public $currency;
    public $url;
    public $click_count;
    public $tags;
    public $type;
    public $locality;
    public $acoodation;
    public $duration;
    public $transport;
    public $diet;
    public $persons;
    public $bonus;
    
    private $opts = array();
    public function initialize() {
        $this->hasMany('id','\Model\Discount\Variant','discount_id',array(
            'alias' => 'variants'
        ));
//        parent::initialize();
        
        $this->hasMany('id','\Model\Discount\Category','discount_id',array(
            'alias' => 'options'
        ));
    }
    
    protected function loadOptions(){
        $options = $this->options;
        foreach($options as $option){
            $items = $option->categories;
            foreach($items as $item){
                if($item->parent_id == 1){
                    if(!isset($this->opts['locality'])){
                        $this->opts['locality'] = array();
                    }
                    $this->opts['locality'][] = $item->name;
                }
                else if($item->parent_id == 40){
                    if(!isset($this->opts['type'])){
                        $this->opts['type'] = array();
                    }
                    $this->opts['type'][] = $item->name;
                }
                else if($item->parent_id == 48){
                    $this->opts['acomodation'] = $item->name;
                }
                else if($item->parent_id == 66){
                    $this->opts['duration'] = $item->name;
                }
                else if($item->parent_id == 67){
                    $this->opts['travel'] = $item->name;
                } 
                else if($item->parent_id == 68){
                    $this->opts['diet'] = $item->name;
                }
                else if($item->parent_id == 69){
                    $this->opts['count'] = $item->name;
                }
                else if($item->parent_id == 88){
                    if(!isset($this->opts['bonus'])){
                        $this->opts['bonus'] = array();
                    }
                    $this->opts['bonus'][] = $item->name;
                } 
            }
        }
    }
    public function getOption($name){
        if(!$this->opts || empty($this->opts)){
            $this->loadOptions();
        }
        
        return $this->opts[$name];
    }
    
    public function getOptions(){
        $translate = array(
            'locality'      => "Lokalita",
            'type'          => "Typ pobytu",
            'acomodation'   => "Ubytování",
            'duration'      => "Délka pobytu",
            'travel'        => "Doprava",
            'diet'          => "Stravování",
            'count'         => "Počet osob",
            'bonus'         => "Bonusy"
        );
        if(!$this->opts || empty($this->opts)){
            $this->loadOptions();
        }
        $result = array();
        foreach($this->opts as $ct => $option){
            $result[$translate[$ct]] = (is_array($option)) ? implode(',', $option) : $option;
        }
        
        return $result;
    }
    
    public function setFilters(array $filters){
        $categories = array();
        
        if(isset($filters['type'])){
            $type = Category::findFirst(array(
                'url = :url:',
                'bind' => array(
                    'url' => $filters['type']
                )
            ));
            
            $categories[] = $type->id;
        }
        
        if(isset($filters['locality'])){
            $locality = Category::findFirst(array(
                'url = :url:',
                'bind' => array(
                    'url' => $filters['locality']
                )
            ));
            
            $categories[] = $locality->id;
        }
        
        unset($filters['price']);
        unset($filters['type']);
        unset($filters['locality']);
        
        foreach($filters as $filter){
            $parts = explode('-',$filter);
            $id = $parts[0];
            $categories[] = $id;
        }
        
        $category = new Discount\Category();
        $category->setFilter('category_id', $categories);
        
        $category->setGroup('discount_id');
        $categories = $category->load(1,99999);
        $ids = array();
        foreach($categories as $ct){
            $ids[] = $ct->discount_id;
        }
        
        
        if(isset($filters['price'])){
            $price = explode("-",$filters['price']);
            $this->setFilter('price', $price[0]);
        }
        $this->setFilter('id', $ids);
    }
}
