<?php


namespace Model;

/**
 * Description of Discount
 *
 * @author Flipixo
 */
class Discount extends \Core\Model {
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
    
    private $opts = array();
    
    private $isSearch = false;
    private $criteria;
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
    
    public function search(array $filters, $page = 1, $itemsPerPage = 9){
        $criteria = $this->getCriteria();
        $bonuses = array();
        foreach($filters as $name => $value){
            if(strpos($name, 'bonus') !== false){
                $parts = explode("-",$value);
                $bonuses[] = $parts[0];
            }
            else if($name === 'locality'){
                $criteria->join('\Model\Discount\Category', 'locality.category_group = 1 AND locality.discount_id = \Model\Discount.id', 'locality');
                $c = Category::findFirst(array(
                    'url = :url:',
                    'bind' => array(
                        'url' => $value
                    )
                ));
                
                $value = $c->id;
                
                $criteria->andWhere('locality.category_id = :'.$name.'category:',array($name.'category' => $value));
            }
            else if($name === 'price'){
                $parts = explode("-",$value);
                $criteria->andWhere('price < :price:',array(
                    'price' => $parts[0]+1
                ));
            }
            else if($name === 'type'){
                $criteria->join('\Model\Discount\Category', 'type.category_group = 39 AND type.discount_id = \Model\Discount.id', 'type');
                $c = Category::findFirst(array(
                    'url = :url:',
                    'bind' => array(
                        'url' => $value
                    )
                ));
                $value = $c->id;
                $criteria->andWhere('type.category_id = :'.$name.'category:',array($name.'category' => $value));
            }
            else if($name === 'order'){
                //todo
                switch($value){
                    case 'nejlevnejsi' :
                        $criteria->orderBy('price ASC');
                    break;
                    case 'nejdrazsi':
                        $criteria->orderBy('price DESC');
                    break;
                    case 'nejprodavanejsi':
                        $criteria->orderBy('customers DESC');
                    break;
                    case 'nejnovejsi':
                        $criteria->orderBy('id DESC');
                    break;
                    case 'nejvetsi-slevy':
                        $criteria->orderBy('discount DESC');
                    break;
                    case 'koncici':
                        $criteria->orderBy('[end] DESC');
                    break;
                    default:
                    break;
                     
                }
            }
            else {
                $parts = explode("-",$value);
                $ct = Category::findFirst($parts[0]);
                $criteria->join('\Model\Discount\Category', $name.'.category_group = '.$ct->parent_id.' AND '.$name.'.discount_id = \Model\Discount.id', $name);
                
                $criteria->andWhere($name.'.category_id = :'.$name.'category:',array($name.'category' => $ct->id));
            }
        }
        
        $page = $page-1;
        $page = $itemsPerPage*$page;
        $criteria->groupBy('\Model\Discount.id');
        $criteria->limit(9,$page);
//        exit;
        $this->isSearch = true;
        $this->criteria = $criteria;
        return $this->criteria->execute();
    }
    
    public function searchs(array $filters){
        $sql = "SELECT discount.* FROM \Model\Discount\Category as discount 
                    JOIN
                            \Model\Discount\Category as locality
                    ON
                            locality.category_group = 1 AND locality.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as type
                    ON
                            type.category_group = 39 AND type.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as acomodation
                    ON
                            acomodation.category_group = 48 AND acomodation.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as duration
                    ON
                            duration.category_group = 66 AND duration.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as transport
                    ON
                            transport.category_group = 67 AND transport.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as diet
                    ON
                            diet.category_group = 68 AND diet.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as persons
                    ON
                            persons.category_group = 69 AND persons.discount_id = discount.id
                    JOIN
                            \Model\Discount\Category as bonus
                    ON
                            bonus.category_group = 88 AND bonus.discount_id = discount.id ";
        
        $i =0;
        foreach($filters as $name => $value){
            $part = explode("-",$value);
            if(!is_int($part[0])){
                $ct = Category::findFirst(array(
                    'url = :url:',
                    'bind' => array(
                        'url' => $value
                    )
                ));
                
                $value = $ct->id;
            }
            else {
                $value = $part[0];
            }
            if($i === 0){
                $sql .= " WHERE ".$name.'.category_id = '.(is_array($value)) ? 'IN ('.implode(',',$value).')' : $value.' ';
            }
            else {
                $sql .= " AND ".$name.'.category_id = '.(is_array($value)) ? 'IN ('.implode(',',$value).')' : $value.' ';
            }
        }
        
        
        $query = $this->modelsManager->executeQuery($sql);
        
        return $query;
    }
}
