<?php
namespace Core\Http;

/**
 * Description of Response
 *
 * @author softdream
 */
class Response extends \Phalcon\Http\Response {
    //put your code here
    
    public function redirect($locationPath = null, $baseUrl = null, $statusCode = null) {
        if(!$locationPath){
            //refresh
            $request = $this->getDI()->getRequest();
            header("Location: ".$request->getURI());
            exit;
        }
        
        if($statusCode){
            $this->setStatusHeader($statusCode);
        }
        
        if(substr($locationPath, 0,1) === '/'){
            $locationPath = substr($locationPath, 1);
        }
        
        header("Location: ".$baseUrl.'/'.$locationPath);
        exit;
    }
    
    public function setStatusHeader($code){
        header("HTTP/1.0 ".$code);
    }
    
    
}
