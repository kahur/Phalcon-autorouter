<?php

use Phalcon\Mvc\Controller;
use Core\Auth;

class ControllerBase extends Controller
{
    protected $authUser;
    public function initialize(){
        
        $this->view->setLayout('light-main');
        

        $this->queueAssets();
    }

    public function queueAssets()
    {
    	/*$this->assets
    	->collection('jsFooter')
    	->setTargetPath('js/min.js')
    	->setTargetUri('js/min.js')
    	->addJs( 'js/prefixfree.min.js' )
    	->addJs( 'js/jquery-1.10.2.min.js' )
    	->addJs( 'js/jquery-ui.js' )
    	->addJs( 'js/bootstrap.min.js' )
		->addJs( 'js/excanvas.min.js' )
		->addJs( 'js/jquery.flot.js' )
		->addJs( 'js/jquery.flot.resize.js' )
		->addJs( 'js/jquery.sparkline.min.js' )
		->addJs( 'js/jquery.hashchange.min.js' )
		->addJs( 'js/jquery.easytabs.min.js' )
		->addJs( 'js/toastr.min.js' )
    	->join(true)
    	->addFilter(new Phalcon\Assets\Filters\Jsmin());

    	$this->assets
    	->collection('cssHead')
    	->setTargetPath('css/min.css')
    	->setTargetUri('css/min.css')
    	->addCss( 'css/font-awesome-4.0.3/css/font-awesome.min.css' )
    	->addCss( 'css/jquery-ui.css' )
    	->addCss( 'css/toastr.css' )
    	->addCss( 'css/bootstrap/bootstrap.css' )
    	->addCss( 'css/style.css' );*/
    }

}
