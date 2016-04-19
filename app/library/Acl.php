<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Acl
 *
 * @author flipixo
 */
class Acl {
    
    /**
     * @var array|\Phalcon\Acl Acl items
     */
    protected $acl;
    
    public function __construct($acl = null) {
        if(!is_array($acl)){
           $this->acl[] = $acl; 
        }
        else {
            $this->acl = $acl;
        }
    }
    
    public function addItem(\Phalcon\Acl $acl){
        $this->acl[] = $acl;
    }
    
    /**
     * @return \Phalcon\Acl\Adapter|array \Phalcon\Acl\Adapter
     */
    public function getAcl($name = 0){
        return (isset($this->acl[$name])) ? $this->acl[$name] : $this->acl;
    }
}
