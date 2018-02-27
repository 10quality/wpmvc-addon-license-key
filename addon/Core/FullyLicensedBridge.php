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
 * @version 1.0.0
 */
class FullyLicensedBridge extends LicensedBridge
{
    /**
     * Called by autoload to init bridge.
     * @since 1.0.0
     *
     * @return void
     */
    public function autoload_init()
    {
        if ( $this->is_valid ) {
            parent::autoload_init();
        } else {
            // Only init this addon
            for ( $i = count( $this->addons ) - 1; $i >= 0; --$i ) {
                if ( isset( $this->addons[$i]->tag ) && $this->addons[$i]->tag === Addon::TAG )
                    $this->addons[$i]->init();
            }
        }
    }
    /**
     * Called by autoload to init bridge on admin.
     * @since 1.0.0
     *
     * @return void
     */
    public function autoload_on_admin()
    {
        if ( $this->is_valid ) {
            parent::autoload_on_admin();
        } else {
            // Only init this addon
            for ( $i = count( $this->addons ) - 1; $i >= 0; --$i ) {
                if ( isset( $this->addons[$i]->tag ) && $this->addons[$i]->tag === Addon::TAG )
                    $this->addons[$i]->on_admin();
            }
        }
    }
    /**
     * Called by autoload to init bridge.
     * @since 1.0.0
     *
     * @return void
     */
    public function add_hooks()
    {
        if ( $this->is_valid )
            parent::add_hooks();
    }
}