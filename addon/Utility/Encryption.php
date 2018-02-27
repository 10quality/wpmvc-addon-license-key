<?php

namespace WPMVC\Addons\LicenseKey\Utility;

/**
 * Encryption utility class.
 *
 * @link https://gist.github.com/niczak/2501891
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.2
 */
class Encryption
{
    /**
     * Safe base 64 encode.
     * @since 1.0.0
     *
     * @param string $string String to encode.
     *
     * @return string
     */
    public static function safe_b64encode( $string )
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    /**
     * Safe base 64 decode.
     * @since 1.0.0
     *
     * @param string $string String to decode.
     *
     * @return string
     */
    public static function safe_b64decode( $string )
    {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
    /**
     * Encode.
     * @since 1.0.0
     * @since 1.0.2 Removed key based encryption for better php support.
     *
     * @param string $string String to encode.
     * @param string $key    Encode key.
     *
     * @return string
     */
    public static  function encode( $value, $key )
    { 
        return trim(self::safe_b64encode($value)); 
    }
    /**
     * Decode.
     * @since 1.0.0
     * @since 1.0.2 Removed key based encryption for better php support.
     *
     * @param string $string String to decode.
     * @param string $key    Decode key.
     *
     * @return string
     */
    public static function decode( $value, $key )
    {
        return trim(self::safe_b64decode($value));
    }
}