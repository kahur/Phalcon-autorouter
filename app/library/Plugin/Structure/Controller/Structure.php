<?php
namespace Plugin\Structure\Controller;

/**
 * Description of Structure
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Structure extends \Phalcon\Mvc\User\Component {
    //put your code here
    
    public function addPageForm(){
        $forms = $this->di->getFormsManager();
        $forms->registerForm(new \Plugin\Structure\Form\AddPageForm());
    }
    
    public function add(){
        if($this->request->isPost()){
            $page = \Core\Model\Factory::create('\Model\Page');
            $page->description = $this->request->getPost('description'); 
            $page->keywords = $this->request->getPost('keywords');
            
            $url = $this->request->getPost("url");
            if(!$url){
                $url = \Core\String::webalize($this->request->getPost('title'));
            }
            $seoUrl = \Plugin\Structure\Model\Url::findFirst(array(
                'url = :url:',
                'bind' => array(
                    'url' => $url
                )
            ));
            
            $urlSeo = new \Plugin\Structure\Model\Url();
            $urlSeo->real_url = \Core\String::webalize($this->request->getPost('title')).'-'.  time();
            $urlSeo->module = 'web';
            $urlSeo->resource = 'index';
            $urlSeo->action = 'index';
            if(!$seoUrl){
                $urlSeo->url = $url;                
            }
            else {
                $urlSeo->save();
                $urlSeo->url = '/'.$urlSeo->id.'-'.substr($url,1);
            }
            
            $urlSeo->save();
            //todo something with url
        }
    }
    public function editPageForm(){
        $forms = $this->di->getFormsManager();
        $forms->registerForm(new \Plugin\Structure\Form\EditPageForm());
    }
    
    public function edit(){
        if($this->request->isPost()){
            $page = \Core\Model\Factory::create('\Model\Page');
            $page->description = $this->request->getPost('description'); 
            $page->keywords = $this->request->getPost('keywords');
            
            $url = $this->request->getPost("url");
            if(!$url){
                $url = \Core\String::webalize($this->request->getPost('title'));
            }
            $seoUrl = \Plugin\Structure\Model\Url::findFirst(array(
                'url = :url:',
                'bind' => array(
                    'url' => $url
                )
            ));
            
            $urlSeo = new \Plugin\Structure\Model\Url();
            $urlSeo->real_url = \Core\String::webalize($this->request->getPost('title')).'-'.  time();
            $urlSeo->module = 'web';
            $urlSeo->resource = 'index';
            $urlSeo->action = 'index';
            if(!$seoUrl){
                $urlSeo->url = $url;                
            }
            else {
                $urlSeo->save();
                $urlSeo->url = '/'.$urlSeo->id.'-'.substr($url,1);
            }
            
            $urlSeo->save();
            //todo something with url
        }
    }
}
