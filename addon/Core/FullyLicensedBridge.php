<?php

namespace WPMVC\Addons\LicenseKey\Core;

use WPMVC\Addons\LicenseKey\LicenseKeyAddon as Addon;

/**
 * Fully Licensed Bridge work only with valid license key.
 * Restricts hooks creation to the activation and validation of a license key.
 *
 * @link http://www.wordpress-mvc.com/v1/add-ons/
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.11
 */
class FullyLicensedBridge extends LicensedBridge
{
    /**
     * Called by autoload to create hooks.
     * Delay add hooks until plugins have loaded
     * @since 1.0.0
     * @since 1.0.8 Delayed hooks creation.
     */
    public function add_hooks()
    {
        add_action( 'plugins_loaded', [&$this, 'protected_hooks'] );
    }
    /**
     * Create hooks.
     * @since 1.0.8
     * @since 1.0.11 Delay validation if license string is not present in database.
     */
    public function protected_hooks()
    {
        if ( $this->addon_is_license_string_valid() === 0 ) {
            // Activate hooks
            parent::add_hooks();
            // Delay validation, license string might have been corrupted or not loaded well.
            // Lets try to validate again during Wordpress `init`.
            add_action( 'init', [&$this, 'addon_license_key_delayed_validation'], 999 );
        } else if ( $this->is_valid ) {
            parent::add_hooks();
        } else {
            add_action( 'admin_notices', [&$this, 'addon_license_key_notice'] );
        }
    }
}