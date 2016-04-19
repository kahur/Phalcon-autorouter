<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Assets
 *
 * @author flipixo
 */
class Assets {
    //put your code here
    
    protected $javascripts = array();
    protected $plainJS = array();
    protected $styles = array();
    protected $others = array();
    
    public function addItem($item,$plain = false){
        if($plain){
            $this->plainJS[] = "<script type='text/javascript'>".$item.'</script>';
        }
        else {
            if(is_array($item)){
                foreach($item as $i){
                    $this->appendItem($i);
                }
            }
            else {
                $this->appendItem($item);
            }
        }
    }
    
    protected function appendItem($item){
        
        if(substr($item, 0,1) === '/'){
            $item = substr($item, 1);
        }
        
        
        if(file_exists($item) || substr($item, 0,2) == '//' || strpos($item, 'http://') !== false || strpos($item, 'https://') !== false){
            $ext = @explode('.',$item);
            $ext = @end($ext);
        
            switch (strtolower($ext)){
                case 'js':
                    $this->addJs($item);
                break;
                case 'css':
                    $this->addCss($item);
                break;
                default:
                    $this->addOther($item);
                break;
            }
        }
        
    }
    
    
    protected function addJs($js){
        if(!in_array($js, $this->javascripts)){
            
            $this->javascripts[] = '/'.$js;
        }
    }
    
    protected function addCss($css){
        if(!in_array($css, $this->styles)){
            $this->styles[] = '/'.$css;
        }
    }
    
    public function outputPlainJS(){
        foreach($this->plainJS as $js){
            echo $js."\n";
        }
    }
    
    protected function addOther($item){
        if(!in_array($item, $this->others)){
            $this->javascripts[] = '/'.$item;
        }
    }
    
    public function outputJS($inHTML = false){
        
        if(!empty($this->javascripts)){
            foreach($this->javascripts as $js){
                
                if(!$inHTML){
                    $js = str_replace(array('/http','///'),array('http','//'),$js);
                ?>
<script type="text/javascript" src="<?php echo $js;?>"></script>
                <?php
                }
                else {
                    ?>
<script type="text/javascript">
    <?php echo readfile($js);?>
</script>
                    <?php
                }
            }
        }
    }
    
    public function outputCSS($inHTML = false){
        if(!empty($this->styles)){
            foreach($this->styles as $style){
                if(!$inHTML){
                    ?>
<link type="text/css" rel="stylesheet" href="<?php echo $style;?>" />
                    <?php
                }
                else {
                    ?>
<style type="text/css">
    <?php echo readfile($style);?>
</style>
                    <?php
                }
            }
        }
    }
    
    public function linkTo($url,$params = array()){
        $href = $url;
//        $accessInfo = $this->acl->getAccessFromUrl($url);
//        $acl = $this->acl->getAcl($accessInfo['module']);
//        $role = $this->acl->roles[$this->auth->group_id];
//        
//        if($acl->isAllowed($role->getName(), str_replace('-','',$accessInfo['resource']), $accessInfo['action'])){
//            
        if(isset($params['js'])){
            $href = $params['js'];
            unset($params['js']);
        }
        
        $inline = null;
        if(isset($params['inContent'])){
            $inline = $params['inContent'];
            unset($params['inContent']);
        }
        
        ?>
        <a href='<?php echo $href;?>'
        <?php
        foreach($params as $name => $value){
            echo $name.'="'.$value.'" ';
        }    
        ?>>
        <?php
        echo $inline;
        ?>
        </a>
        <?php
//        }
    }
}
