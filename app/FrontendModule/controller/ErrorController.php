<?php

namespace Frontend\Controller;
/**
 * Description of ErrorController
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class ErrorController extends ControllerBase {
    public function error404Action(){
	$this->flash->error('Error 404 page not found');
	$this->response->setStatusCode(404, 'Page not found');
    }
}

