<?php
namespace Core\Plugin\Setup;

/**
 * Description of Setup
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Form extends \Phalcon\Forms\Form {
    final public function initialize(){
        $check = new \Phalcon\Forms\Element\Check('validation');
        $check->addValidator(new \Phalcon\Validation\Validator\PresenceOf(array(
            'message' => 'Instalace nemůže pokračovat, je vyžadované potvzení, správnosti instalačních údajů'
        )));
    }
    
    final public function customFields(array $fields){
        foreach($fields as $field){
            $element = new \Phalcon\Forms\Element\Text($field['propName']);
            $element->addValidator(new \Phalcon\Validation\Validator\PresenceOf(array(
                'message' => 'Nezadali jste položku '.$field['name']
            )));
            
            $this->add($element);
        }
    }
}
