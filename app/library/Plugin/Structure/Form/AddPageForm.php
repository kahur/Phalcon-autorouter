<?php
namespace Plugin\Structure\Form;

/**
 * Description of AddFOrm
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class AddPageForm extends \Phalcon\Forms\Form {
    public $settings = array(
        'formName' => 'SEO',
        'position' => 'side-panel'
    );
    
    //put your code here
    public function initialize(){
        $keywords = new \Phalcon\Forms\Element\Text('keywords',array(
            'class' => 'form-control'
        ));       
        $keywords->setLabel('Klíčové slova');
        $this->add($keywords);
        
        $description = new \Phalcon\Forms\Element\Text('description',array(
            'class' => 'form-control'
        )); 
        $description->setLabel('Popis/Description');
        $this->add($description);
        
        $url = new \Phalcon\Forms\Element\Text('url',array(
            'class' => 'form-control'
        )); 
        $url->setLabel('URL');
        $this->add($url);
    }
}
