<?php
namespace Core\File;

/**
 * Description of Manager
 *
 * @author Kamil Hurajt
 */
class Manager {
    //put your code here
    /**
     * @var \Phalcon\Http\Request\File
     */
    protected $resource;
    public $file;    
    public $type;
    public $size;
    public $extension;
    
    private $error;
    
    public function __construct(\Phalcon\Http\Request\File $file) {
        $this->resource = $file;
        $this->file = $file->getName();
//        $this->type = $file->getRealType();
        $this->size = $file->getSize();
        $this->extension = $file->getExtension();
    }
    
    /**
     * Save file to directory
     * @param String $path Path to directory to store file
     * @return mixed false when something is wrong, string with path to file when the file is successfuly saved
     */
    public function save($path){
        try {
            $directory = new \Core\Directory\Manager($path);
            if(!$directory->isWritable()){
                $this->error = 'The directory is not writeable.';
                return false;
            }
            
            $path = $directory->getPath().md5(time().$this->file).'.'.$this->extension;
            if(!$this->resource->moveTo($path)){
                $this->error = 'Cannot save file to directory '.$path;
                return false;
            }
            
            return $path;
            
        }
        catch(\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * @return String|NULL Return current error while execution script
     */
    public function getError(){
        return $this->error;
    }
    
    
}
