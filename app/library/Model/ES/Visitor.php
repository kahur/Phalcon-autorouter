<?php
namespace Model\ES;

/**
 * Description of Visitor
 *
 * @author softdream
 */
class Visitor extends \Core\Model\ES {
    //put your code here
    protected $_type = 'visitor';
    
    public $id;
    public $visitor;
    public $IP;
    public $site;
    public $url;
    public $created_at;
}
