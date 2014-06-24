<?php namespace Frontend\Controller;

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
        
    protected function initialize(){
	$this->view->setLayout('index');
    }
}
