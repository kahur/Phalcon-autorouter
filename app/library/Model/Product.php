<?php
namespace Model;

/**
 * Description of Product
 *
 * @author webdev
 */
class Product extends \Core\Model {
    //put your code here
    public $id;
    public $created_at;
    public $updated_at;
    public $item_id;
    public $title;
    public $price;
    public $original_price;
    public $currency;
    public $discount;
    public $place_address;
    public $place_gps;
    public $original_url;
    public $description;
}
