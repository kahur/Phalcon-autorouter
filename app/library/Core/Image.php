<?php


/**
 * Description of Image
 *
 * @author Kamil Hurajt <kamil@webowebu.cz>
 * @copyright (c) 2015 Kamil Hurajt <kamil@flipixo.com>, 
 * @license http://www.webowebu.cz/socket-server/license/
 * @version 1.0
 */
class Image {
    //put your code here
    private $error;
    protected $source;
    /**
     * @var \Core\File\Adapter\Image
     */
    protected $image;
    
    public $name;
    public $size;
    public $width;
    public $height;
    public $type;
    
    public function __construct($file) {
        $this->source = $file;
        //file from files
        try {
            if(isset($file['tmp_name'])){
                $this->image = \Core\File\Adapter\Image::fromFile($file['tmp_name'],$file['type']);
                $this->name = $file['name'];
                $this->size = $file['size'];
                $this->type = $file['type'];
            }
            else if(file_exists($file)) {//file from path
                $this->image = \Core\File\Adapter\Image::fromFile($file);
            }
            else {//file from string
                $this->image = \Core\File\Adapter\Image::fromString($file);
            }
            
            $this->loadImageInfo();
        }
        catch(\Exception $e){
            $this->image = false;
            $this->error = 'Unsuported format or file source.';
            return false;
        }
    }
    
    protected function loadImageInfo(){
        if(!isset($this->source['tmp_name']) && file_exists($this->source)){
            $fileInfo = pathinfo($this->source);
//            $this->name = $this->image->get
        }
    }
    
    public function getError(){
        return $this->error;
    }
}
