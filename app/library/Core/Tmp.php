<?php
namespace Core;

/**
 * Description of Tmp
 *
 * @author Kamil Hurajt, Flipixo
 */
class Tmp {
    //put your code here
    
    public static function init($authUserId, $uri){
        $tmp = \Model\Tmp::findFirst(array(
            'user_id = :user: AND url = :uri:',
            'bind' => array(
                'user' => $authUserId,
                'uri'  => $uri
            )            
        ));
        
        if(!$tmp){
            $tmp = new \Model\Tmp();
            $tmp->user_id = $authUserId;
            $tmp->url = $uri;
            $tmp->created_at = date("Y-m-d H:i:s",time());
            $tmp->save();
        }
        
        $tmp->setDefaults();
        
        return $tmp;
    }
}
