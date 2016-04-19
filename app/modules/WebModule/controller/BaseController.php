<?php
namespace Web\Controller;

use Core\Auth;
/**
 * Description of BaseController
 *
 * @author softdream
 */
class BaseController extends \ControllerBase {
    
   public function initialize(){        
        $this->view->setLayout('main');        
    }
}