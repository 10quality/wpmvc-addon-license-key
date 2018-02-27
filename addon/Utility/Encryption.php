<?php

namespace WPMVC\Addons\LicenseKey\Utility;

/**
 * Encryption utility class.
 *
 * @link https://gist.github.com/niczak/2501891
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.0
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
     *
     * @param string $string String to encode.
     * @param string $key    Encode key.
     *
     * @return string
     */
    public static  function encode( $value, $key )
    { 
        if(!$value){return false;}
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, str_pad($key, 16, 'x'), $text, MCRYPT_MODE_ECB, $iv);
        return trim(self::safe_b64encode($crypttext)); 
    }
    /**
     * Decode.
     * @since 1.0.0
     *
     * @param string $string String to decode.
     * @param string $key    Decode key.
     *
     * @return string
     */
    public static function decode( $value, $key )
    {
        if(!$value){return false;}
        $crypttext = self::safe_b64decode($value); 
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, str_pad($key, 16, 'x'), $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }
}