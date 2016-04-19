<?php
namespace Model\Lang;


/**
 * Description of List
 *
 * @author softdream
 */
class Source extends \Core\Model {
    //put your code here
    public $id;
    public $name;
    public $hash;
    
    public function initialize(){
        $this->setSource('lang_source');
        $this->hasMany('id', '\Model\Lang', 'source_id');
    }
    
}
