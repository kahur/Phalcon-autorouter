<?php
namespace Core;

use Core\File\Image;

/**
 * Description of File
 *
 * @author softdream
 */
class File {
    
    
    
    /**
     * @return \Core\File\Adapter return file adapter by file
     */
    public static function load($file){
        
    }
    
    /**
     * @param \Core\File\Adapter|string $adapter Adapter name or adapter object
     * @return \Core\File\Adapter return created blank file adapter
     */
    public static function create($adapter = null){
        
        if($adapter instanceof \Core\File\Adapter){
            return $adapter;
        }
        else {
            $adapter = '\Core\File\Adapter\\'.strtolower(ucfirst($adapter));
            if(!class_exists($adapter)){
                throw new \Core\File\Exception('Unsuported adapter '.$adapter);
            }
            
            return $adapter::fromBlank();
        }
    }
    
    /**
     * @param string|array file or list of files to delete
     * @return boolean Description
     */
    public static function delete($file){
        
    }
    
    
}
