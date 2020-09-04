<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use stdClass;
use Exception;
use TenQuality\WP\File
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
 * @version 2.0.3
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
        $handler = $this->main->config->get( 'license_api.handler' );
        if ( empty( $frequency ) )
            $frequency = LicenseRequest::DAILY_FREQUENCY;
        // Validate
        return Api::activate(
            Client::instance()->set( $this->get_client_options() ),
            function() use( &$url, &$store_code, &$sku, &$license_key, &$frequency, &$handler ) {
                return LicenseRequest::create(
                    $url,
                    $store_code,
                    $sku,
                    $license_key,
                    $frequency,
                    $handler
                );
            },
            [&$this, 'encrypt_save']
        );
    }
    /**
     * Validates activated license key.
     * @since 1.0.0
     *
     * @param object $main  Main class reference.
     * @param bool   $force Flag that forces validation against the server.
     *
     * @return bool
     */
    public function validate( $main, $force = false )
    {
        $this->main = $main;
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Prepare connection retries
        $allow_retry = $this->main->config->get( 'license_api.allow_retry' );
        $retry_attempts = intval( $this->main->config->get( 'license_api.retry_attempts' ) );
        $retry_frequency = $this->main->config->get( 'license_api.retry_frequency' );
        // Validate
        return Api::validate(
            Client::instance()->set( $this->get_client_options() ),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            [&$this, 'encrypt_save'],
            $force, // Force
            $allow_retry === null ? true : $allow_retry,
            $retry_attempts ? $retry_attempts : 2,
            $retry_frequency ? $retry_frequency : '+1 hour'
        );
    }
    /**
     * Validates activated license key (soft validation, non cross-server).
     * @since 1.1.7
     *
     * @param object $main  Main class reference.
     *
     * @return bool
     */
    public function soft_validate( $main )
    {
        $this->main = $main;
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Validate
        return Api::softValidate(
            function() use( &$license ) {
                return new LicenseRequest( $license );
            }
        );
    }
    /**
     * Returns validation endpoint's response to check license key status.
     * @since 2.0.0
     *
     * @param object $main Main class reference.
     *
     * @return object
     */
    public function check( $main )
    {
        $this->main = $main;
        $license = $this->load_decrypt();
        if ( $license === false )
            return false;
        // Validate and return response
        return Api::check(
            Client::instance()->set( $this->get_client_options() ),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            [&$this, 'encrypt_save']
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
        $response = Api::deactivate(
            Client::instance()->set( $this->get_client_options() ),
            function() use( &$license ) {
                return new LicenseRequest( $license );
            },
            [&$this, 'encrypt_save']
        );
        if ( $response->error === true ) {
            $this->encrypt_save( null );
            // Force deactivation
            $response->error = false;
            $response->message = __( 'Deactivated.', 'wpmvc-addon-license-key' );
        }
        return $response;
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
        $license_key = $this->load_decrypt();
        return $license_key !== false ? json_decode( $license_key ) : $license_key;
    }
    /**
     * Returns flag indicating if license string is empty or not.
     * @since 1.0.11
     *
     * @param object $main Main class reference.
     *
     * @return mixed|bool|int Returns flag if license string is present, retuns 0 if no string is found in wordpress.
     */
    public function is_valid( $main )
    {
        $this->main = $main;
        $license = $this->load_decrypt();
        if ($license === 0)
            return null;
        if ($license === false)
            return false;
        $license = new LicenseRequest( $license );
        return !$license->isEmpty;
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
        $license = get_option( $this->main->config->get( 'license_api.option_name' ), 0 );
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
     *
     * @param string $license License string to save.
     *
     * @param mixed|null|string $license License string.
     */
    public function encrypt_save( $license )
    {
        // Check for downloadbles and updates
        $new = json_decode( $license );
        $old = $this->get( $this->main );
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
        if ( $old !== false
            && isset( $new->data->downloadable )
            && isset( $old->data->downloadable )
            && $new->data->downloadable->name !== $old->data->downloadable->name
        ) {
            // Update is available
            update_option(
                $this->main->config->get( 'updater.option' ),
                0,
                true //autoload
            );
        }
    }
    /**
     * Returns available client options.
     * @since 2.0.3
     *
     * @return array
     */
    private function get_client_options()
    {
        $cookie_path = $this->main->config->get( 'paths.cookies' );
        if ( empty( $cookie_path ) )
            $cookie_path = WP_CONTENT_DIR . '/wpmvc/cookies';
        $cookie_filename = $cookie_path . '/lk_cookie.txt';
        $file = File::auth();
        if ( !$file->is_dir( $cookie_path ) ) {
            $file->mkdir( $cookie_path );
        }
        // Return options
        return [
            CURLOPT_COOKIEFILE => $cookie_filename,
            CURLOPT_COOKIEJAR => $cookie_filename,
        ];
    }
}