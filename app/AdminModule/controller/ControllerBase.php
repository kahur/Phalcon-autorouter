<?php 
namespace Admin\Controller;

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
        
    public function initialize(){
	$this->view->setTemplateBefore('main');
	$this->view->setLayout('index');
    }
}
