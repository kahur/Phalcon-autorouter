<?php //
namespace Core\File;

/**
 * Description of Adapter
 *
 * @author softdream
 */
abstract class Adapter {
    
    private $_resource;
    private $_contents;
    protected $info;
    private static $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
	    'mp4' => 'video/mp4',
	    'flv' => 'video/x-flv',
	    'f4v' => 'video/x-flv',
	    'avi' => 'video/x-msvideo',
	    '3gp' => 'video/3gp',
	    'wmv' => 'video/x-ms-wmv',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
    private $extension;

    public function __construct($file) {
	$this->setFileResource($file);
    }
    
    private function setFileResource($file){
	if(is_array($file)){
	    $this->_resource = $file['name'];
	}
	else {
	    $this->_resource = $file;
	}
    }
    
   public function setExtension($ext){
	$this->extension = $ext;
    }
    
    public function getFileType(){
	return self::getMimeType($this->getFileExtension());
    }
    
    public function getFileExtension(){
	if(!$this->extension)
	{
	    $pathinfo = basename($this->info['name']);
	    $ext = '';
	    if(isset($pathinfo['extension'])){
		$ext = $pathinfo['extension'];
	    }
	    else {
		$name = basename($this->info['name']);
		if(substr_count($name,'.') > 0)
		{
		    $ext = end(explode('.',$name));
		}
	    }
	    
	    $this->extension = $ext;
	}
	return $this->extension;
    }
    
    public function getFileResource(){
	return $this->_resource;
    }
    
    public function getFileSize(){
	return filesize($this->getFileResource());
    }

    
    public function getFileContents(){
	if(!$this->_contents){
	    $this->setFileContents();
	}
	
	return $this->_contents;
    }
    
    private function setFileContents(){
	ob_start();
	$this->save();
	$this->_contents = ob_end_clean();
    }
    
    public static function getMimeType($extension){
	$extension = strtolower($extension);
	
	
	
	return isset(self::$mime_types[$extension]) ? self::$mime_types[$extension] : 'application/occet-stream';
    }
    
    public static function getExtensionByMimeType($mimeType){
	$mimes = array_flip(self::$mime_types);
	
	return isset($mimes[strtolower($mimeType)]) ? $mimes[strtolower($mimeType)] : null;
    }
    
    
    
    abstract public function save();
    
        
}
