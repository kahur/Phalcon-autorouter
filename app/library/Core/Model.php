<?php
namespace Core;


/**
 * Description of Abstract
 *
 * @author flipixo
 * @version 1.1
 */
abstract class Model extends \Phalcon\Mvc\Model {
    /**
     * @var array stored filters configuration
     * @deprecated since version 1.1 new variable is $filter
     */
    protected $filters = array();
    /**
     * @var array stored filters configuration
     */
    public $filter = array();
    
    /**
     * @var array prepare post condition
     */
    public $having = array();
    
    /** 
     * @var array order conditions
     */
    protected $order = array();
    
    /**
     * @var array group by conditions
     */
    protected $group = array();
    
    /**
     * @var array filter to display columns
     */
    protected $columns = array();
    
    /**
     * @var \Phalcon\Paginator\Adapter\Model
     */
    protected $pagination;
    
    /**
     * @var \Phalcon\Mvc\Model\Criteria
     * @deprecated since version 1.1
     */
    protected $query;
    
    /**
     * @var array query result
     */
    protected $queryResult;
    
    
    protected function buildSimpleWhereItem($key,$value,$i){
        $where = ' ';
        $binds = array();
        if(is_array($value[0])) {		    
            if($value[1] == 'OR' || $value[1] == '||')
            {
                $where .= ' AND ';
                
                $where .= " (";
                foreach ($value[0] as $vKey => $val) {
                    $where .= $key . ' = :' . $key . '-OR-'.$vKey.': OR ';  
                    $binds[$key.'-OR-'.$vKey] = $val;
                }

                $where = substr($where, 0, -4);
                $where .= ")";
            }
            else if($value[1] == '='){
                
                $where .= ' AND ';
                
                $where .= ' '.$key. ' IN (';
                $vCount = count($value[0])-1;
                $vKey = 0;
                foreach($value[0] as $val){
                    $where .= (is_int($val)) ? $val : '\''.$val.'\'';
                    if($vKey < $vCount){
                        $where .= ',';
                    }

//                    $binds[$key.'IN'.$vKey] = $val;
                    $vKey++;
                }                        
                $where .= ' ) ';                        
            }
            else if($value[1] == '!=' || $value[1] == '<>') {
                
                $where .= ' AND ';
                $where .= ' '.$key. ' NOT IN (';
                $vCount = count($value[0]);
                foreach($value[0] as $vKey => $val){
                    $where .= (is_int($val)) ? $val : '\''.$val.'\'';
                    if($vKey < $vCount){
                        $where .= ',';
                    }

//                    $binds[$key.'NOTIN'.$vKey] = $val;
                }                        
                $where .= ' ) '; 
            }
        } else {
                $where .= ' AND ';
                
                $where .= $key . ' '.$value[1].' ';
                $where .=  ' :'.$key.$i.': ';
                $binds[$key.$i] = $value[0];
        }
        
        return array('where' => $where, 'bind' => $binds);
    }
    
    
    protected function buildGroupedWhereItem($groupName,array $groupFilters,$i = 1){
        $where = ' ';
        $binds = array();
        foreach($groupFilters as $index => $filter){
                //without glue
            if($index === 0){
                $where .= $filter['glue']['outside'].' ( ';
            }
            if(is_array($filter['value'])){
                foreach($filter['value'] as $index1 => $value){
                    if($index1 === 0){
                        $where .= $filter['column'].' IN ( ';
                        $where .= ' \''.$value.'\' ';
                    }
                    else {
                        $where .= ', \''.$value.'\' ';
                    }
                }
                
                $where .= ' ) ';
            }
            else {
                if($index > 0){
                    $where .= ' '.$filter['glue']['inside'].' ';
                }
                
                $where .= ' '.$filter['column'].' '.$filter['operator'].' :'.$filter['column'].$groupName.$index.': ';
                $binds[$filter['column'].$groupName.$index] = $filter['value'];
            }
        }
        
        $where .= ' ) ';
        
        return array('where' => $where,'bind' => $binds);
    }
    
    protected function buildGroupedHavingItem($groupName,array $groupFilters,$i = 1){
        $where = ' ';
        $binds = array();
        foreach($groupFilters as $index => $filter){
                //without glue
            if($index === 0){
                $where .= $filter['glue']['outside'].'  ( ';
                $where .= $filter['column'].' '.$filter['operator'].' :'.$filter['column'].$groupName.$index.': ';
            }
            else {
                $where .= $filter['glue']['inside'].' '.$filter['column'].' '.$filter['operator'].' :'.$filter['column'].$groupName.$index.': ';
            }
            
            $binds[$filter['column'].$groupName.$index] = $filter['value'];
        }
        
        $where .= ' ) ';
        
        return array('where' => $where,'bind' => $binds);
    }
    
    /**
     * Create 'having' condition for SQL query
     * @return array('condition' => (string),'binds' => (array))
     */
    protected function getHaving(){            
        $where = 'HAVING \'A\' = \'A\' ';
        $binds = array();
        $i = 0;
        foreach($this->filter as $key => $value) {
                if(is_array($value) && isset($value['isGrouped'])){
                    $w = $this->buildGroupedWhereItem($key, $value['filters'],$i);
                    $where .= $w['where'];
                    $binds += $w['bind'];
                }
                else {
                    $w = $this->buildSimpleWhereItem($key, $value, $i);
                    $where .= $w['where'];
                    $binds += $w['bind'];
                    
                }
                $i++; 
        }
        

        return array('condition' => $where,'bind' => $binds);    
    }
    
    /**
     * Create 'where' condition for SQL query
     * @return array('condition' => (string),'binds' => (array))
     */
    protected function getWhere(){            
        $where = '\'A\' = \'A\' ';
        $binds = array();
        $i = 0;
        foreach($this->filter as $key => $value) {
                if(is_array($value) && isset($value['isGrouped'])){
                    $w = $this->buildGroupedWhereItem($key, $value['filters'],$i);
                    $where .= $w['where'];
                    $binds += $w['bind'];
                }
                else {
                    $w = $this->buildSimpleWhereItem($key, $value, $i);
                    $where .= $w['where'];
                    $binds += $w['bind'];                    
                }
                $i++; 
        }
        
        if (!empty($this->_customCondition)) {
            $where .= ' AND ' . str_replace(array("WHERE","WHERE"),array("",""),$this->_customCondition);
        }

        return array('condition' => $where,'bind' => $binds);    
    }
    
    /**
     * Build order clause
     * @return String prepared order by condition
     */
    protected function getOrder(){
        $orderWhere = '';
        $count = count($this->order)-1;
        $i = 0;
        foreach($this->order as $name => $order){
            $order = strtoupper($order);
            if($order !== 'ASC' && $order !== 'DESC'){
                $order = "ASC";
            }
            
            $orderWhere .= $name.' '.$order;
            if($count > $i){
                $orderWhere .= ',';
            }
            
            $i++;
        }
        
        return $orderWhere;
    }
    
    /**
     * Setting order condition
     * @param $name String column name
     * @param $order String ASC|DESC
     */
    public function setOrder($name,$order = 'ASC'){
        $this->order[$name] = $order;
    }
    
    /**
     * Setting group condition
     * @param $name String column name
     */
    public function setGroup($name){
        if(!in_array($name, $this->group)){
            $this->group[] = $name;
        }
    }
    
    /**
     * Pagination
     * @return \Phalcon\Pagination\Adapter\Model
     */
    public function getPaginator(){
        return $this->pagination;
    }
    
    
    
    /**
     * Build request parameters for ::find Phalcon function
     * @return array
     */
    protected function buildRequest(){
        $params = array(
            'conditions'=> null,
            'bind'      => null,
            'columns'   => null,
            'order'     => 'id DESC',
            'group'     => null
        );
        
        if(!empty($this->filter)){
            $where = $this->getWhere();
            $params['conditions'] = $where['condition'];
            $params['bind']       = $where['bind'];
        }
        
        if(!empty($this->columns)){
            $params['columns'] = implode(",",$this->columns);
        }
        
        if(!empty($this->order)){
            $params['order'] = $this->getOrder();
        }
        
        if(!empty($this->group)){
            $params['group'] = implode(',',$this->group);
        }
        
//        exit;
        return $params;
        
        
    }
    
    
    /**
     * Load items
     * @return \stdClass with result set
     */
    public function load($page = 1, $itemsPerPage = 10){
        
            $params = $this->buildRequest();
//            if($itemsPerPage === 12){
//                echo "<pre>";
//                var_dump($params);
//                exit;
//            }
            
//            $params['limit'] = array('number' => $itemsPerPage,'offset' => $page);
            $result = parent::find($params);
            $this->pagination = new \Phalcon\Paginator\Adapter\Model(array(
                'data'  => $result,
                'limit' => $itemsPerPage,
                'page'  => $page
            ));

            return $this->pagination->getPaginate()->items;        
    }
    
    /**
     * Load count items
     * @return int
     */    
    public function loadCount($debug = false){
        $params = $this->buildRequest();
        
        $result =  parent::count($params)->count();
        if($result instanceof \Phalcon\Mvc\Model\Resultset\Simple){
            return $result->count();
        }
        
        
        if($debug){
            echo "<pre>";
            var_dump($params);
            var_dump($result);
            exit;
        }
        
        return $result;
        
    }
    
    /**
     * @return \Phalcon\Paginator\Adapter\Model
     */
    public function getPagination(){
        if(!$this->pagination && $this->query){           
        
            $builder = $this->query;
            $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(
                array(
                    "builder" => $builder,
                    "limit" => 1,
                    "page" => 1
                )
            );

            $result = $paginator->getPaginate();

            return $result;
    
        }
        
        return $this->pagination->getPaginate();
    }
    
    /**
     * @return \Phalcon\Mvc\Model\ResultsetInterface
     * @deprecated since version 1.1 use find
     */
    public function findFiltered() {
        if(!empty($this->filters) || $this->query){
            $query = $this->buildQuery();
            return $query->execute();
        }
        return parent::find();
    }
    
    
    /**
     * @param string $name Column name
     * @param string|array $value When value is array filter will be set as ( column = value1 OR column = value2 .. ) 
     * @param string Operator ( =,>,<,<> )
     * @return Model Description
     */
    public function setFilter($name,$value,$operator = '=',$group = null, $glues = array('inside' => 'AND','outside' => 'AND')){        
        if($value && $value !== ""){
            if(!$group){                
                    $this->filter[$name] = array($value, $operator);                
            }
            else {
                if(!isset($this->filter[$group])){
                    $this->filter[$group] = array(
                        'isGrouped' => true,
                        'filters' => array()
                    );
                }
                
                $this->filter[$group]['filters'][] = array(
                    'column'    => $name,
                    'value'     => $value,
                    'operator'  => $operator,
                    'glue'      => $glues
                );
            }
        }
    }
    

    
    /**
     * Add column to load
     * @return Model Description
     */
    public function addColumn($name){
        if(!in_array($name, $this->columns)){
            $this->columns[] = $name;
        }        
        return $this;
    }
    
    /**
     * Add column to load
     * @return Model Description
     */
    public function addColumns(array $columnsNames){
        foreach($columnsNames as $column){
            $this->addColumn($column);
        }
    }
    
    public function getCriteria(){
        if(!$this->query){
            $this->query = $this->query();
        }
        return $this->query;
    }
    
    //depreacted
            
    /**
     * @param string $name Column name
     * @param string|array $value When value is array filter will be set as ( column = value1 OR column = value2 .. ) 
     * @param string Operator ( =,>,<,<> )
     * @return Model Description
     * @deprecated since version 1.1 use setFilter
     */
    public function addFilter($name,$value,$operator = '=',$isOr = false){  
        
        $vars = get_object_vars($this);
        if(!is_array($value))
        {
            if(array_key_exists($name, $vars) && $value != -1 && $value !== '')
            {
                $this->filters[$name] = array(
                    'query' => $name.' '.$operator.' :'.$name.':',
                    'param' => array($name => $value),
                    'isOr'  => $isOr
                );
            }
        }
        else {
            $query = '';
            $params = array();
            $filterId = $name;
            $i = 0;
            foreach($value as $key => $fieldValue){
                if(array_key_exists($fieldValue, $vars) && $fieldValue != -1 && $fieldValue !== ''){
                    if($operator === '=' || $operator === '<>')
                    {
                        if($i === 0){
                            $query .= $name;
                            $query .= ($operator === '=') ?  ' IS IN ( ' : ' IS NOT IN ( ';
                            $query .= ':'.$name.$key.': ';
                        }                    
                        else {
                            $query .= ', :'.$name.$key.': ';
                        }
                    }
                    else {
                        $query .= $name.' '.$operator.' :'.$name.$key.': ';
                    }
                    
                    $params[$name.$key] = $fieldValue;
                    $filterId .= $fieldValue;
                    $i++;
                }
            }
            if(!empty($query) && !empty($params))
            {
                $query .= ' ) ';
                $this->filters[$filterId] = array(
                    'query' => $query,
                    $params => $params,
                    'isOr'  => $isOr
                );
            }
        }
        
        return $this;
    }    
        
    /**
     * @return \Phalcon\Mvc\Model\Criteria Description
     * @deprecated since version 1.1
     */
    protected function buildQuery(){
        if(!$this->query){
             $this->query = $this->query();
        }
        
        $query = $this->query;
        
        if(!empty($this->columns)){
            $query->columns($this->columns);
        }
        
        if(!empty($this->filters))
        {
            foreach($this->filters as $queryData){
                if($queryData['isOr']){
                    $query->orWhere($queryData['query'],$queryData['param']);
                }
                else {
                    $query->andWhere($queryData['query'],$queryData['param']); 
                }
            }
        }
        
        return $query;
    }
    
    public function delete($items = null) {
        if($items){
            foreach($items as $item){
                $item->delete();
            }
        }
        else {
            parent::delete();
        }
    }
    
    public function loadQuery($page = 1,$itemsPerPage = 1000){
        $builder = $this->query;
        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(
            array(
                "builder" => $builder,
                "limit" => $itemsPerPage,
                "page" => $page
            )
        );
        
        $result = $paginator->getPaginate();
        
        return $result;
    }
    
    public function getQuery(){
        return $this->query;
    }
  
}
