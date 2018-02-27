<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use Exception;
use Defuse\Crypto\Crypto;
use LicenseKeys\Utility\Api;
use LicenseKeys\Utility\Client;
use LicenseKeys\Utility\LicenseRequest;
use WPMVC\MVC\Controller;

/**
 * License key controller.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.0
 */
class LicenseController extends Controller
{
    /**
     * Activates a license key.
     * @since 1.0.0
     *
     * @param string $license_key License key to activate.
     *
     * @return object
     */
    public function activate( $license_key )
    {
        if ( empty( $license_key ) )
            throw new Exception('License Key can not be empty.');
        // Get config 
        $url = $this->main->config->get('license_api.url');
        $store_code = $this->main->config->get('license_api.store_code');
        $sku = $this->main->config->get('license_api.sku');
        $frequency = $this->main->config->get('license_api.frequency');
        if ( empty( $frequency ) )
            $frequency = LicenseRequest::DAILY_FREQUENCY;
        // Validate
        return Api::activate(
            Client::instance(),
            function() use( &$url, &$store_code, &$sku, &$license_key, &$frequency ) {
                return LicenseRequest::create(
                    $url,
                    $store_code,
                    $sku,
                    $license_key,
                    $frequency
                );
            },
            $this->encrypt_save
        );
    }
    /**
     * Validates activated license key.
     * @since 1.0.0
     *
     * @return bool
     */
    public function validate()
    {
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Validate
        return Api::validate(
            Client::instance(),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            $this->encrypt_save
        );
    }
    /**
     * Deactivates activated license key.
     * @since 1.0.0
     *
     * @return mixed|bool|object
     */
    public function deactivate()
    {
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Validate
        return Api::deactivate(
            Client::instance(),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            $this->encrypt_save
        );
    }
    /**
     * Returns license string stored at Wordpress options.
     * Decrypts license prior returning.
     * @since 1.0.0
     *
     * @return mixed|string|bool
     */
    private function load_decrypt()
    {
        // Load
        $license = get_option( $this->main->config->get('license_api.option_name'), false );
        // Decrypt
        if ( is_string( $license ) )
            return Crypto::decrypt( $license, $this->main->config->get('license_api.ck') );
        return $license;
    }
    /**
     * Saves and encrypts license string into Wordpress options.
     * @since 1.0.0
     *
     * @param mixed|null|string $license License string.
     */
    private function encrypt_save( $license )
    {
        update_option(
            $this->main->config->get('license_api.option_name'),
            is_string( $license )
                ? Crypto::encrypt( $license, $this->main->config->get('license_api.ck') )
                : false,
            true //autoload
        );
    }
}