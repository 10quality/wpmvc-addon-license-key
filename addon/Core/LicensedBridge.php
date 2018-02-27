<?php

namespace WPMVC\Addons\LicenseKey\Core;

use WPMVC\Bridge;

/**
 * Licensed Bridge enables license key validations.
 * Extends WPMVC bridge to provide license key and protection coverage.
 *
 * @link http://www.wordpress-mvc.com/v1/add-ons/
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.0
 */
class LicensedBridge extends Bridge
{
    /**
     * Returns flag indicating if bridge is valid.
     * Valid bridge means that an activated license key is valid.
     * @since 1.0.0
     *
     * @var null|bool
     */
    protected static $is_valid;
    /**
     * Overrides parent getter method.
     * Returns READ-ONLY properties.
     * @since 1.0.2
     *
     * @param string $property Property name.
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ( $property === 'is_valid' ) {
            if ( ! isset( self::$is_valid ) )
                self::$is_valid = $this->addon_is_license_key_valid();
            return self::$is_valid;
        }
        return parent::__get( $property );
    }
}