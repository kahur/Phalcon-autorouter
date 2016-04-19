<?php
namespace Model\Discount;

/**
 * Description of Category
 *
 * @author Flipixo
 */
class Category extends \Core\Model {
    //put your code here
    public $id;
    public $discount_id;
    public $category_group;
    public $category_id;
    
    public function initialize() {
        $this->setSource('discount_category');
        $this->hasMany('category_id','\Model\Category','id',array(
            'alias' => 'categories'
        ));
    }
}
