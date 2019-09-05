<?php

namespace WPMVC\Addons\LicenseKey\Core;

use WPMVC\Bridge;

/**
 * Licensed Bridge enables license key validations.
 * Extends WPMVC bridge to provide license key and protection coverage.
 * LicenseTrait must be present to function.
 *
 * @see WPMVC\Addons\LicenseKey\Traits\LicenseTrait 
 * @link http://www.wordpress-mvc.com/v1/add-ons/
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.12
 */
class LicensedBridge extends Bridge
{
    /**
     * Overrides parent getter method.
     * Returns READ-ONLY properties.
     * @since 1.0.2
     * @since 1.0.12 Access `is_valid` through LicenseTrait.
     *
     * @param string $property Property name.
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ( $property === 'is_valid' ) {
            return $this->_isset_is_valid()
                ? $this->_is_valid()
                : $this->_set_is_valid( $this->addon_is_license_key_softvalid() );
        }
        return parent::__get( $property );
    }
}