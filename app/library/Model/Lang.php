<?php
namespace Model;
/**
 * Description of Lang
 *
 * @author softdream
 */
class Lang extends \Core\Model {
    //put your code here
    public $id;
    public $source_id;
    public $name;
    public $flag;
    
    public function initialize()
    {        
    	$this->hasOne('source_id', '\Model\Lang\Source', 'id');
    }
}
