<?php
namespace Core\Flash;

/**
 * Description of Session
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 * 
 */
class Session extends \Phalcon\Flash\Session {
//    protected $emptyItems = array(
//	'notice'    => array(),
//	'error'	    => array(),
//	'success'   => array(),
//	'warning'   => array()
//    );
//    
//    protected function addItem($message,$type){
//	$session = $this->getDI()->getSession();
//	if(!$session->isStarted()){
//	    $session->start();
//	}
//	
//	$items = ($session->has('flash')) ? $session->get('flash') : $this->emptyItems;
//	
//        $lang = $this->getDI()->getLang();
//	if($message instanceof \Phalcon\Validation\Message){
//            $msg = $message->getMessage();
//            $msg = $lang->translate((string)$msg);
//	    array_push($items[$type], $msg);
//	}
//	else {
//            $msg = $lang->translate((string)$message);
//	    array_push($items[$type], $msg);
//	}
//	
//	$session->set("flash",$items);
//    }
//    
//    public function warning($message) {
//	$this->addItem($message, 'warning');
//    }
//    
//    public function notice($message) {
//	$this->addItem($message, 'notice');
//    }
//    
//    public function error($message) {
//	$this->addItem($message, 'error');
//    }
//    
//    public function success($message) {
//	$this->addItem($message, 'success');
//    }
//    
//    protected function _setSessionMessages() {
//    }
//    
//    protected function _getSessionMessages() {
//	$session = $this->getDI()->getSession();
//	$items = array();
//	if($session->has('flash')){
//	    $items =  $session->get("flash");
//	}
////	$session->remove('flash');
//	return $items;
//    }
//    
//    public function getMessages($a = null,$b = null){
//	return $this->_getSessionMessages();
//    }
//    
//    protected function remove(){
//	$session = $this->getDI()->getSession();
//	if($session->has('flash'))
//	{
//	    $session->remove('flash');
//	}
//    }
//    public function output($remove = null) {
////	parent::ou
//	$items = $this->_getSessionMessages();
//	$html = '';
//	foreach($items as $type => $messages){
//	    foreach($messages as $message){
////		parent::ou
//		parent::outputMessage($type, $message);
//	    }
//	}
////	var_dump($html);
//	$this->remove();
//	
//    }
    
}

