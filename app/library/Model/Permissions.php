<?php
namespace Model;

/**
 * Description of Permissions
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Permissions extends \Core\Model {
    public $id;
    public $group_id;
    public $module;
    public $resource;
    public $action;
    public $created_at;
    public $updated_at;
}

