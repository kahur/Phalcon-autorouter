<?php
namespace Plugin\Structure\Model;

/**
 * Description of Url
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Url extends \Core\Model {
    //put your code here
    public $id;
    public $url;
    public $real_url;
    public $module;
    public $resource;
    public $action;
    
    public function initialize(){
        $this->setSource('seo_url');
    }
}
