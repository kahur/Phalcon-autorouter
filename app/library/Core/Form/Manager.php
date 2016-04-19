<?php
namespace Core\Form;

/**
 * Description of Manager
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Manager {
    //put your code here
    /**
     * @var \Phalcon\Forms\Form[]
     */
    protected $forms;
    protected $form;
    
    protected $formInfo = array();
    
    protected $errors;
    
    /**
     * Add new form into form manager
     * @param \Phalcon\Forms\Form $form Prepared instance of form
     * @param String|int $name Name or identification for form, with this name will be accessible
     */
    public function registerForm(\Phalcon\Forms\Form $form, $name = null){
//        $form->ha
        $this->forms[] = $form;
        
        $this->formInfo[] = array(
            'name'      => $form->settings['formName'],
            'position'  => $form->settings['position'], 
            'fields'    => $form->getElements(),
            'form'      => $form
        );
    }
    
    /**
     * Check if forms is valid
     * @return boolean
     */
    public function isValid(array $data){
        $valid = true;
        foreach($this->forms as $form){
            
            if(!$form->isValid($data)){
                if($form->getMessages()->count() > 0){
                    if(!$this->errors){
                        $this->errors = $form->getMessages();
                    }
                    else {
                        $this->errors = (object) array_merge((array) $this->errors, (array)$form->getMessages());
                    }
                }
                
                $valid = false;
            }
        }
        
        return $valid;
    }
    
   
    
    /**
     * @return array return error messages if exists
     */
    public function getMessages(){
        return $this->errors;
    }
    
    public function appanedMessage(\Phalcon\Validation\MessageInterface $message){
        $this->errors[] = $message;
    }
    
    public function render($name,array $options = null){
        foreach($this->forms as $form){
            if($form->has($name)){
                return $form->render($name,$options);
            }
        }
    }
    
     /**
      * Load and return list of form fields
      * @return array Description
      */
    protected function getFormFields(\Phalcon\Forms\Form $form){
        $fields = $form->getElements();
        $fieldNames = array();
        foreach($fields as $field){
            $fieldNames[] = $field->getName();
        }
        
        return $fieldNames;
    }
    
    public function getForms(){
        $forms = array_reverse($this->formInfo);
        return $forms;
    }
    
}
