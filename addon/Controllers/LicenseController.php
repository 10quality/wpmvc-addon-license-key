<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use Closure;
use Exception;
use LicenseKeys\Utility\Api;
use LicenseKeys\Utility\Client;
use LicenseKeys\Utility\LicenseRequest;
use WPMVC\MVC\Controller;
use WPMVC\Addons\LicenseKey\Utility\Encryption;

/**
 * License key controller.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.6
 */
class LicenseController extends Controller
{
    /**
     * Main class reference.
     * @since 1.0.0
     * @var object
     */
    protected $main;
    /**
     * Activates a license key.
     * @since 1.0.0
     *
     * @param string $license_key License key to activate.
     * @param object $main        Main class reference.
     *
     * @return object
     */
    public function activate( $license_key, $main )
    {
        $this->main = $main;
        if ( empty( $license_key ) )
            throw new Exception( 'License Key can not be empty.' );
        // Get config 
        $url = $this->main->config->get( 'license_api.url' );
        $store_code = $this->main->config->get( 'license_api.store_code' );
        $sku = $this->main->config->get( 'license_api.sku' );
        $frequency = $this->main->config->get( 'license_api.frequency' );
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
            Closure::fromCallable( [&$this, 'encrypt_save'] )
        );
    }
    /**
     * Validates activated license key.
     * @since 1.0.0
     *
     * @param object $main Main class reference.
     *
     * @return bool
     */
    public function validate( $main )
    {
        $this->main = $main;
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Validate
        return Api::validate(
            Client::instance(),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            Closure::fromCallable( [&$this, 'encrypt_save'] )
        );
    }
    /**
     * Deactivates activated license key.
     * @since 1.0.0
     *
     * @param object $main Main class reference.
     *
     * @return mixed|bool|object
     */
    public function deactivate( $main )
    {
        $this->main = $main;
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Validate
        return Api::deactivate(
            Client::instance(),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            Closure::fromCallable( [&$this, 'encrypt_save'] )
        );
    }
    /**
     * Returns license string only if activated.
     * @since 1.0.0
     *
     * @param object $main Main class reference.
     *
     * @return object
     */
    public function get( $main )
    {
        $this->main = $main;
        if ( $main->is_valid )
            return json_decode( $this->load_decrypt() );
    }
    /**
     * Returns license string stored at Wordpress options.
     * Decrypts license prior returning.
     * @since 1.0.0
     *
     * @return mixed|string|bool
     */
    protected function load_decrypt()
    {
        // Load
        $license = get_option( $this->main->config->get( 'license_api.option_name' ), false );
        // Decrypt
        if ( is_string( $license ) )
            return Encryption::decode(
                $license,
                $this->main->config->get( 'license_api.store_code' )
            );
        return $license;
    }
    /**
     * Saves and encrypts license string into Wordpress options.
     * @since 1.0.0
     * @since 1.0.4 Handles updates checking.
     * @since 1.0.6 Fixes downloadable structure.
     *
     * @param string $license License string to save.
     *
     * @param mixed|null|string $license License string.
     */
    protected function encrypt_save( $license )
    {
        // Check for downloadbles and updates
        $new = json_decode( $license );
        $old = $this->get( $this->main );
        if ( $old !== null
            && isset( $new->data->downloadable )
            && isset( $old->data->downloadable )
            && $new->data->downloadable->name !== $old->data->downloadable->name
        ) {
            // Update is available
            update_option(
                $this->main->config->get( 'updater.option' ),
                false,
                true //autoload
            );
        }
        // Save license string
        update_option(
            $this->main->config->get( 'license_api.option_name' ),
            is_string( $license )
                ? Encryption::encode(
                    $license,
                    $this->main->config->get( 'license_api.store_code' )
                )
                : false,
            true //autoload
        );
    }
}