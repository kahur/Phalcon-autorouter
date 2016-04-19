<?php
namespace Core;

/**
 * Description of Color
 *
 * @author softdream
 */
class Color {
    //put your code here
   /**
     * Returns RGB color.
     *
     * @param  int  red 0..255
     * @param  int  green 0..255
     * @param  int  blue 0..255
     * @param  int  transparency 0..127
     * @return array
     */
    public static function rgb($red, $green, $blue, $transparency = 0)
    {
        return array(
            'red' => max(0, min(255, (int) $red)),
            'green' => max(0, min(255, (int) $green)),
            'blue' => max(0, min(255, (int) $blue)),
            'alpha' => max(0, min(127, (int) $transparency)),
        );
    }
}
