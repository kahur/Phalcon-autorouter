<?php
namespace Core;

/**
 * Description of String
 *
 * @author softdream
 */
final class String {
    public function __construct() {
        ;
    }
    
    
    public static function random($min = 4,$max = 10){
        $str = 'ABCDEFGHIJKLMNOPQRSTXYZabcdefghijklmnopqrstuvxyz0123456789';
        $string = '';
        $max = rand($min,$max);
        for($i = 1;$i <= $max; $i++){
            $string .= $str[rand(0,strlen($str)-1)];
        }
        
        return $string;
    }
    
    public static function urlToCamelFormat($string, $firstCamel = false){
        if(strpos($string, '-') !== false && $string !== null){
	    $tmpString = '';
	    $stringParts = explode("-",$string);
	    foreach($stringParts as $key => $part){
		if($key === 0){
		    $tmpString .= ($firstCamel === true) ? ucfirst(strtolower($part)) : strtolower($part);
		}
		else {
		    $tmpString .= ucfirst(strtolower($part));
		}
	    }
	    
	    return $tmpString;
	}
	
	return ($firstCamel) ? ucfirst(strtolower($string)) : strtolower($string);
    }
    
    public static function camelFormatToUrl($string){
        return strtolower(preg_replace('|([A-Z])|s', "-$1",$string));
    }
    
    /**
     * Truncates string to maximal length.
     *
     * @param  string  UTF-8 encoding
     * @param  int
     * @param  string  UTF-8 encoding
     * @return string
     */

    public function truncate($s, $maxLen,$removeTags = false, $append = "\xE2\x80\xA6") {
        if($removeTags){
            $s = strip_tags($s);
        }
        if ($this->length($s) > $maxLen) {
            $maxLen = $maxLen - $this->length($append);
            if ($maxLen < 1) {
                return $append;
            } elseif ($matches = $this->match($s, '#^.{1,' . $maxLen . '}(?=[\s\x00-/:-@\[-`{-~])#us')) {
                return $matches[0] . $append;
            } else {
                return iconv_substr($s, 0, $maxLen, 'UTF-8') . $append;
            }
        }

        return $s;

    }
    
    /**
     * Returns UTF-8 string length.
     *
     * @param  string
     * @return int
     */

    public function length($s) {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen(utf8_decode($s));
    }
    
    /**
     * Performs a regular expression match.
     *
     * @param  string
     * @param  string
     * @param  int
     * @param  int
     * @return mixed
     */

    public function match($subject, $pattern, $flags = 0, $offset = 0) {

        $res = preg_match($pattern, $subject, $m, $flags, $offset);
        if ($res) {
            return $m;
        }
    }
    
    static public function webalize($s, $charlist = NULL, $lower = TRUE, $separator = '-') {

        $s = self::toAscii($s);
        if ($lower)
            $s = strtolower($s);
        
        $s = preg_replace('#[^a-z0-9' . preg_quote($charlist, '#') . ']+#i', $separator, $s);
        $s = trim($s, '-');

        return $s;

    }
    
    /**

     * Converts to ASCII.

     *

     * @param  string  UTF-8 encoding

     * @return string  ASCII

     */

    static public function toAscii($s) {

        $s = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u', '', $s);

        $s = strtr($s, '`\'"^~', "\x01\x02\x03\x04\x05");

        if (ICONV_IMPL === 'glibc') {

            $s = @iconv('UTF-8', 'WINDOWS-1250//TRANSLIT', $s); // intentionally @

            $s = strtr($s, "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2"

                            . "\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe",

                            "ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt");

        } else {

            $s = @iconv('UTF-8', 'ASCII//TRANSLIT', $s); // intentionally @

        }

        $s = str_replace(array('`', "'", '"', '^', '~'), '', $s);

        return strtr($s, "\x01\x02\x03\x04\x05", '`\'"^~');

    }
}
