<?php
namespace Model\User;
/**
 * Description of Settings
 *
 * @author HP
 */
class Settings extends \Core\Model {
    //put your code here
    
    public $id;
    public $user_id;
    public $news_frequency;
    public $last_sent;
    public $news_categories;
    
    public function initialize(){
        $this->setSource('user_settings');
        $this->hasOne('user_id', '\Model\User', 'id', array(
            'alias' => 'user'
        ));
    }
    
    public function createDefaults($user_id){
        $categories = array(
            '40',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46',
            '47',
            '61',
            '62',
            '65'
        );
        $this->user_id = $user_id;
        $this->news_frequency = 7;
        $this->news_categories = serialize($categories);
        $this->save();
    }
}
