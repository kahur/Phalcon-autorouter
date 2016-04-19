<?php

namespace Model\Discount;

/**
 * Description of Variant
 *
 * @author Flipixo
 */
class Variant extends \Core\Model {
    //put your code here
    public $id;
    public $discount_id;
    public $title;
    public $price;
    public $original_price;
    public $url;
    
    public function initialize() {
        $this->setSource('discount_variant');
//        parent::initialize();
    }
}
