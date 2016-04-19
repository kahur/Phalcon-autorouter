<?php
namespace Model\ES;

/**
 * Description of Session
 *
 * @author softdream
 */
class Session extends \Core\Model\ES {
    //put your code here
    protected $_type = 'session';
    public $id;
    public $visitor;
    public $message;
    public $url;    
    public $site; 
    public $date;
    
    public function test(){
        $this->getParams();
    }
    
}
