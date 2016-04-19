<?php
namespace Model\ES;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Conversations
 *
 * @author softdream
 */
class Conversation extends \Core\Model\ES {
    //put your code here
    protected $_type = 'Conversation';
    public $id;
    public $session_id;
    public $visitor_id;
    public $operator_id;
    public $message;
    public $date;
    
}
