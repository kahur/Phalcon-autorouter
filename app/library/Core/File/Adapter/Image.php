<?php

namespace Core\File\Adapter;

/**
 * Description of Image
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Softdream, Webvizitky
 * @package name
 * @category name
 *
 */
class Image extends \Core\File\Adapter {

    /**

     * Allows enlarging image (it only shrinks images by default)

     * @link resize()

     */
    const ENLARGE = 1;

    /**
     * Will ignore aspect ratio
     * @link resize()
     */
    const STRETCH = 2;

    /**
     * Fits in given area
     * @link resize()
     */
    const FIT = 0;

    /**
     * Fills (and even overflows) given area
     * @link resize()
     */
    const FILL = 4;

    /**
     * @int image types
     * @link send()
     */
    const GIF = 1;
    const JPEG = 2;
    const PNG = 3;

    /**
     * Empty gif
     */
    const EMPTY_GIF = "GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\x00\x00\x00!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";

    /**
     * @var bool
     */
    public static $useImageMagick = FALSE;

    /**
     * @var resource
     */
    private $image = null;

    /**
     * Returns RGB color.
     *
     * @param  int  red 0..255
     * @param  int  green 0..255
     * @param  int  blue 0..255
     * @param  int  transparency 0..127
     * @return array
     */
    protected $info;

    public static function rgb($red, $green, $blue, $transparency = 0) {

        return array(
            'red' => max(0, min(255, (int) $red)),
            'green' => max(0, min(255, (int) $green)),
            'blue' => max(0, min(255, (int) $blue)),
            'alpha' => max(0, min(127, (int) $transparency)),
        );
    }

    /**

     * Opens image from file.

     *

     * @param  string

     * @param  mixed  detected image format

     * @throws \Core\File\Adapter\Exception

     * @return \Core\File\Adapter\Image|Core_Image_Magick

     */
    public static function fromFile($file, & $format = NULL) {
        if(substr($file,0,1) === '/'){
            $file = substr($file,1);
        }
        if (!extension_loaded('gd')) {

            throw new \Core\File\Adapter\Exception("PHP extension GD is not loaded.");
        }


        $info = @getimagesize($file); // @ - files smaller than 12 bytes causes read error
        $fileInfo = array();
        $fileInfo['basename'] = basename($file);
        $fileInfo['pathInfo'] = pathinfo($file);
        $fileInfo['imagesize'] = $info;
        if (!$info && !$format) {
            $format = null;
            $ext = strtolower($fileInfo['pathInfo']['extension']);
            switch ($ext) {
                case 'gif':
                    $format = self::GIF;
                    break;
                case 'jpg':
                    $format = self::JPEG;
                    break;
                case 'png':
                    $format = self::PNG;
                    break;
            }
        } else if (!$format) {
            $format = $info[2];
        }
//	else
//	var_dump($fileInfo);
        switch ($format) {
            case self::JPEG:
                return new self(imagecreatefromjpeg($file), $fileInfo);
            case self::PNG:
                return new self(imagecreatefrompng($file), $fileInfo);
            case self::GIF:
                return new self(imagecreatefromgif($file), $fileInfo);
            default:


                throw new \Core\File\Adapter\Exception("Unknown image type or file '$file' not found.");
        }
    }

    /**

     * Get format from the image stream in the string.

     *

     * @param  string

     * @return mixed  detected image format

     */
    public static function getFormatFromString($s) {

        if (strncmp($s, "\xff\xd8", 2) === 0) {

            return self::JPEG;
        }



        if (strncmp($s, "\x89PNG", 4) === 0) {

            return self::PNG;
        }



        if (strncmp($s, "GIF", 3) === 0) {

            return self::GIF;
        }



        return NULL;
    }

    /**

     * Create a new image from the image stream in the string.

     *

     * @param  string

     * @param  mixed  detected image format

     * @throws \Core\File\Adapter\Exception

     * @return Core_Image

     */
    public static function fromString($s, & $format = NULL) {

        if (!extension_loaded('gd')) {



            throw new \Core\File\Adapter\Exception("PHP extension GD is not loaded.");
        }



        $format = self::getFormatFromString($s);



        return new self(imagecreatefromstring($s));
    }

    /**

     * Creates blank image.

     *

     * @param  int

     * @param  int

     * @param  array

     * @throws \Core\File\Adapter\Exception

     * @return Core_Image

     */
    public static function fromBlank($width, $height, $color = NULL) {
        if (!extension_loaded('gd')) {

            throw new \Core\File\Adapter\Exception("PHP extension GD is not loaded.");
        }

        $width = (int) $width;
        $height = (int) $height;
        if ($width < 1 || $height < 1) {

            throw new \Core\File\Adapter\Exception('Image width and height must be greater than zero.');
        }

        $image = imagecreatetruecolor($width, $height);

        if (is_array($color)) {
            $color += array('alpha' => 0);

            $color = imagecolorallocatealpha($image, $color['red'], $color['green'], $color['blue'], $color['alpha']);

            imagealphablending($image, FALSE);

            imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);

            imagealphablending($image, TRUE);
        }



        return new self($image);
    }

    /**

     * Wraps GD image.

     *

     * @param  resource

     */
    public function __construct($image, $info = null) {
        $this->info = $info;
        parent::__construct($image);
        $this->setImageResource();
    }

    /**

     * Returns image width.

     *

     * @return int

     */
    public function getWidth() {

        return imagesx($this->image);
    }

    /**

     * Returns image height.

     *

     * @return int

     */
    public function getHeight() {

        return imagesy($this->image);
    }

    public static function hex2rgb($color) {

        if (substr_count($color, "#") > 0) {

            $color = str_replace("#", "", $color);
        }



        list($r, $g, $b) = array($color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]);

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }

    /**

     * Sets image resource.

     *

     * @param  resource

     * @throws \Core\File\Adapter\Exception

     * @return Core_Image  provides a fluent interface

     */
    protected function setImageResource() {
        $resource = $this->getFileResource();
        if (!is_resource($resource) || get_resource_type($resource) !== 'gd') {



            throw new \Core\File\Adapter\Exception('Image is not valid.');
        }

        $this->image = $resource;



        return $this;
    }

    /**

     * Returns image GD resource.

     *

     * @return resource

     */
    public function getImageResource() {

        return $this->image;
    }

    /**

     * Resizes image.

     *

     * @param  mixed  width in pixels or percent

     * @param  mixed  height in pixels or percent

     * @param  int    flags

     * @return Core_Image  provides a fluent interface

     */
    public function resize($width, $height, $flags = self::FIT) {

        list($newWidth, $newHeight) = self::calculateSize($this->getWidth(), $this->getHeight(), $width, $height, $flags);



        if ($newWidth !== $this->getWidth() || $newHeight !== $this->getHeight()) { // resize
            $newImage = self::fromBlank($newWidth, $newHeight, self::RGB(0, 0, 0, 127))->getImageResource();

            imagecopyresampled(
                    $newImage, $this->getImageResource(), 0, 0, 0, 0, $newWidth, $newHeight, $this->getWidth(), $this->getHeight()
            );

            $this->image = $newImage;
        }



        if ($width < 0 || $height < 0) { // flip is processed in two steps for better quality
            $newImage = self::fromBlank($newWidth, $newHeight, self::RGB(0, 0, 0, 127))->getImageResource();

            imagecopyresampled(
                    $newImage, $this->getImageResource(), 0, 0, $width < 0 ? $newWidth - 1 : 0, $height < 0 ? $newHeight - 1 : 0, $newWidth, $newHeight, $width < 0 ? -$newWidth : $newWidth, $height < 0 ? -$newHeight : $newHeight
            );

            $this->image = $newImage;
        }



        return $this;
    }

    /**

     * Calculates dimensions of resized image.

     *

     * @param  mixed  source width

     * @param  mixed  source height

     * @param  mixed  width in pixels or percent

     * @param  mixed  height in pixels or percent

     * @param  int    flags

     * @throws \Core\File\Adapter\Exception

     * @return array

     */
    public static function calculateSize($srcWidth, $srcHeight, $newWidth, $newHeight, $flags = self::FIT) {

        if (substr($newWidth, -1) === '%') {

            $newWidth = round($srcWidth / 100 * abs($newWidth));

            $flags |= self::ENLARGE;

            $percents = TRUE;
        } else {

            $newWidth = (int) abs($newWidth);
        }



        if (substr($newHeight, -1) === '%') {

            $newHeight = round($srcHeight / 100 * abs($newHeight));

            $flags |= empty($percents) ? self::ENLARGE : self::STRETCH;
        } else {

            $newHeight = (int) abs($newHeight);
        }



        if ($flags & self::STRETCH) { // non-proportional
            if (empty($newWidth) || empty($newHeight)) {



                throw new \Core\File\Adapter\Exception('For stretching must be both width and height specified.');
            }



            if (($flags & self::ENLARGE) === 0) {

                $newWidth = round($srcWidth * min(1, $newWidth / $srcWidth));

                $newHeight = round($srcHeight * min(1, $newHeight / $srcHeight));
            }
        } else {  // proportional
            if (empty($newWidth) && empty($newHeight)) {



                throw new \Core\File\Adapter\Exception('At least width or height must be specified.');
            }



            $scale = array();

            if ($newWidth > 0) { // fit width
                $scale[] = $newWidth / $srcWidth;
            }



            if ($newHeight > 0) { // fit height
                $scale[] = $newHeight / $srcHeight;
            }



            if ($flags & self::FILL) {

                $scale = array(max($scale));
            }



            if (($flags & self::ENLARGE) === 0) {

                $scale[] = 1;
            }



            $scale = min($scale);

            $newWidth = round($srcWidth * $scale);

            $newHeight = round($srcHeight * $scale);
        }



        return array((int) $newWidth, (int) $newHeight);
    }

    /**

     * Crops image.

     *

     * @param  mixed  x-offset in pixels or percent

     * @param  mixed  y-offset in pixels or percent

     * @param  mixed  width in pixels or percent

     * @param  mixed  height in pixels or percent

     * @return Core_Image  provides a fluent interface

     */
    public function crop($left, $top, $width, $height) {
        list($left, $top, $width, $height) = self::calculateCutout($this->getWidth(), $this->getHeight(), $left, $top, $width, $height);

        $newImage = self::fromBlank($width, $height, self::RGB(0, 0, 0, 127))->getImageResource();

        imagecopy($newImage, $this->getImageResource(), 0, 0, $left, $top, $width, $height);
        $this->image = $newImage;

        return $this;
    }

    /**

     * Calculates dimensions of cutout in image.
     *
     * @param  mixed  source width

     * @param  mixed  source height

     * @param  mixed  x-offset in pixels or percent

     * @param  mixed  y-offset in pixels or percent

     * @param  mixed  width in pixels or percent

     * @param  mixed  height in pixels or percent

     * @return array

     */
    public static function calculateCutout($srcWidth, $srcHeight, $left, $top, $newWidth, $newHeight) {

        if (substr($newWidth, -1) === '%') {

            $newWidth = round($srcWidth / 100 * $newWidth);
        }

        if (substr($newHeight, -1) === '%') {

            $newHeight = round($srcHeight / 100 * $newHeight);
        }

        if (substr($left, -1) === '%') {

            $left = round(($srcWidth - $newWidth) / 100 * $left);
        }

        if (substr($top, -1) === '%') {

            $top = round(($srcHeight - $newHeight) / 100 * $top);
        }

        if ($left < 0) {

            $newWidth += $left;
            $left = 0;
        }

        if ($top < 0) {

            $newHeight += $top;
            $top = 0;
        }

        $newWidth = min((int) $newWidth, $srcWidth - $left);

        $newHeight = min((int) $newHeight, $srcHeight - $top);



        return array($left, $top, $newWidth, $newHeight);
    }

    /**

     * Sharpen image.

     *

     * @return Core_Image  provides a fluent interface

     */
    public function sharpen() {

        imageconvolution($this->getImageResource(), array(// my magic numbers ;)

            array(-1, -1, -1),
            array(-1, 24, -1),
            array(-1, -1, -1),
                ), 16, 0);



        return $this;
    }

    /**

     * Puts another image into this image.

     * @param  Core_Image

     * @param  mixed  x-coordinate in pixels or percent

     * @param  mixed  y-coordinate in pixels or percent

     * @param  int  opacity 0..100

     * @return Core_Image  provides a fluent interface

     */
    public function place(Core_Image $image, $left = 0, $top = 0, $opacity = 100) {

        $opacity = max(0, min(100, (int) $opacity));



        if (substr($left, -1) === '%') {

            $left = round(($this->getWidth() - $image->getWidth()) / 100 * $left);
        }



        if (substr($top, -1) === '%') {

            $top = round(($this->getHeight() - $image->getHeight()) / 100 * $top);
        }



        if ($opacity === 100) {

            imagecopy($this->getImageResource(), $image->getImageResource(), $left, $top, 0, 0, $image->getWidth(), $image->getHeight());
        } elseif ($opacity <> 0) {

            imagecopymerge($this->getImageResource(), $image->getImageResource(), $left, $top, 0, 0, $image->getWidth(), $image->getHeight(), $opacity);
        }



        return $this;
    }

    /**

     * Saves image to the file.

     *

     * @param  string  filename

     * @param  int  quality 0..100 (for JPEG and PNG)

     * @param  int  optional image type

     * @throws \Core\File\Adapter\Exception

     * @return bool TRUE on success or FALSE on failure.

     */
    public function save($file = NULL, $quality = NULL, $type = NULL) {
        if(file_exists($file)){
            return true;
        }
        if ($type === NULL) {

            switch (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {

                case 'jpg':

                case 'jpeg':

                    $type = self::JPEG;

                    break;

                case 'png':

                    $type = self::PNG;

                    break;

                case 'gif':

                    $type = self::GIF;
                    break;
                default:
                    $type = self::PNG;
                    break;
            }
        }



        switch ($type) {
            case self::JPEG:
                $quality = $quality === NULL ? 85 : max(0, min(100, (int) $quality));
                return imagejpeg($this->getImageResource(), $file, $quality);
                break;
            case self::PNG:
                $quality = $quality === NULL ? 9 : max(0, min(9, (int) $quality));
                return imagepng($this->getImageResource(), $file, $quality);
                break;
            case self::GIF:
                return $file === NULL ? imagegif($this->getImageResource()) : imagegif($this->getImageResource(), $file); // PHP bug #44591

                break;

            default:

                $quality = $quality === NULL ? 9 : max(0, min(9, (int) $quality));

                return imagepng($this->getImageResource(), $file, $quality);
                break;
        }
    }

    /**

     * Outputs image to string.

     *

     * @param  int  image type

     * @param  int  quality 0..100 (for JPEG and PNG)

     * @return string

     */
    public function toString($type = self::JPEG, $quality = NULL) {

        ob_start();

        $this->save(NULL, $quality, $type);

        return ob_get_clean();
    }

    /**

     * Outputs image to string.

     *

     * @return string

     */
    public function __toString() {

        return $this->toString();
    }

    /**

     * Outputs image to browser.

     *

     * @param  int  image type

     * @param  int  quality 0..100 (for JPEG and PNG)

     * @throws \Core\File\Adapter\Exception

     * @return bool TRUE on success or FALSE on failure.

     */
    public function send($type = self::JPEG, $quality = NULL) {

        if ($type !== self::GIF && $type !== self::PNG && $type !== self::JPEG) {
            throw new \Core\File\Adapter\Exception("Unsupported image type.");
        }

        header('Content-Type: ' . image_type_to_mime_type($type));
        return $this->save(NULL, $quality, $type);
    }

    /**

     * Call to undefined method.
     * @param  string  method name
     * @param  array   arguments
     * @return mixed
     * @throws MemberAccessException
     */
    public function __call($name, $args) {
        try {
            $function = 'image' . $name;

            if (function_exists($function)) {

                foreach ($args as $key => $value) {
                    if ($value instanceof self) {
                        $args[$key] = $value->getImageResource();
                    } elseif (is_array($value) && isset($value['red'])) { // rgb
                        $args[$key] = imagecolorallocatealpha($this->getImageResource(), $value['red'], $value['green'], $value['blue'], $value['alpha']);
                    }
                }

                array_unshift($args, $this->getImageResource());
                $res = call_user_func_array($function, $args);

                return is_resource($res) && get_resource_type($res) === 'gd' ? $this->setImageResource($res) : $res;
            }


            echo $name;
            exit;
            return parent::__call($name, $args);
        } catch (Exception $e) {
            echo $name;
            exit;
        }
    }

    public static function getImage($path, $base64 = false, $w = null, $h = null, $default = '/images/noimage.jpg') {
        $imageName = basename($path);
        $path = str_replace($imageName, '', $path);
        $imageParts = explode('.', $imageName);
        $ext = end($imageParts);
        $fullpath = $path . $imageName;
        if(substr($path,0,1) === '/'){
            $path = substr($path, 1);
        }
        if (file_exists(substr($fullpath,1)) && !file_exists($path . $w . '-' . $h . $imageName)) {
            if ($w && $h) {
                $image = self::fromFile($fullpath);
                if ($image->getWidth() > $w && $image->getHeight() > $h) {
                    $image->resize($w, $h, self::FILL);
                    $fullpath = $path . $w . '-' . $h . $imageName;
                    try {
                        $image->save($fullpath);
                    } catch (Exception $e) {
                        throw $e;
                    }
                }
            }

            if ($base64) {
                return 'data:' . $image->getMimeType($ext) . ';base64,' . base64_encode(file_get_contents($fullpath));
            }
        }
        else if(!file_exists(substr($fullpath,1))){
            return $default;
        }
        else {
            $fullpath = $path . $w . '-' . $h . $imageName;
        }

        return '/' . $fullpath;
    }

}
