<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use stdClass;
use WPMVC\Request;
use WPMVC\MVC\Controller;

/**
 * License key controller.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.1.0
 */
class SettingsController extends Controller
{
    /**
     * Flag that prevents registration duplicates.
     * @since 1.0.12
     *
     * @var bool
     */
    private static $has_registered = false;
    /**
     * Returns action links.
     * @since 1.0.0
     *
     * @param array  $links
     * @param object $main Main class reference.
     *
     * @return array
     */
    public function action_links( $links, $main )
    {
        $ref = $main->config->get( 'type' ) === 'plugin'
            ? strtolower( $main->config->get( 'namespace' ) )
            : 'theme';
        $links[] = '<a href="'.admin_url( '/admin.php?page=addon-manage-license-key&ref='.$ref ).'">'
            .__( 'Manage License Key', 'addon' )
            .'</a>';
        return $links;
    }
    /**
     * Registers admin page.
     * @since 1.0.0
     * @since 1.0.12 Prevents double registering.
     *
     * @param object $main Main class reference.
     *
     * @return array
     */
    public function admin_menu( $main )
    {
        if ( !static::$has_registered ) {
            if ( $main->config->get( 'type' ) === 'plugin' ) {
                add_submenu_page(
                    null,
                    __( 'Manage License Key', 'wpmvc-addon-license-key' ),
                    __( 'License Key', 'wpmvc-addon-license-key' ),
                    'manage_options',
                    'addon-manage-license-key',
                    [&$this, 'display_page']
                );
            } else if ( $main->config->get( 'type' ) === 'theme' ) {
                add_theme_page(
                    __( 'Manage License Key', 'wpmvc-addon-license-key' ),
                    __( 'License Key', 'wpmvc-addon-license-key' ),
                    'manage_options',
                    'addon-manage-license-key',
                    [&$this, 'display_page']
                );
            }
            static::$has_registered = true;
        }
    }
    /**
     * Displays manage page.
     * @since 1.0.0
     * @since 1.0.1 Validates reference.
     * @since 1.0.9 Activated flag added, validation action added.
     * @since 1.1.0 Fixes validation.
     *
     * @return array
     */
    public function display_page()
    {
        // Get global variable
        $ref = Request::input( 'ref', 'theme' );
        global $$ref;
        $errors = [];
        $response = null;
        $license_key = null;
        $activated = false;
        if ( $$ref !== null ) {
            // Handle actions
            if ( Request::input( 'action' ) === 'activate' ) {
                $license_key = trim( Request::input( 'license_key' ) );
                if ( empty( $license_key ) ) {
                    $errors['license_key'] = [__( 'License Key is required.', 'wpmvc-addon-license-key' )];
                } else {
                    $response = $$ref->addon_activate_license_key( $license_key );
                    if ( isset( $response->error ) && $response->error === false )
                        $activated = true;
                    if ( isset( $response->errors ) )
                        $errors = $response->errors;
                }
            }
            if ( Request::input( 'action' ) === 'deactivate' ) {
                $response = $$ref->addon_deactivate_license_key();
                if ( isset( $response->errors ) )
                    $errors = $response->errors;
            }
            if ( Request::input( 'action' ) === 'validate' ) {
                $is_valid = $$ref->addon_is_license_key_valid( true );
                $response = new stdClass;
                $response->error = !$is_valid;
                if ( $is_valid ) {
                    $response->message = __( 'License Key is valid.', 'wpmvc-addon-license-key' );
                } else {
                    $response->errors = [ 'activation_id' => [ __( 'License Key is invalid.', 'wpmvc-addon-license-key' ) ] ];
                }
                if ( isset( $response->errors ) )
                    $errors = $response->errors;
            }
            // Show
            $this->view->show( 'admin.manage-page', [
                'main'          => $$ref,
                'license'       => $$ref->addon_get_license_key(),
                'errors'        => $errors,
                'response'      => $response,
                'license_key'   => $license_key,
                'ref'           => $ref,
                'activated'     => $activated,
            ] );
        } else {
            $this->view->show( 'admin.not-found', [
                'ref'           => $ref,
            ] );
        }
    }
    /**
     * Loads text domain for localization.
     * @since 1.1.0
     *
     * @param object $main Main class reference.
     */
    public function load_textdomain( $main )
    {
        load_plugin_textdomain(
            'wpmvc-addon-license-key',
            false,
            $main->config->get( 'paths.root_folder' ) . '/vendor/10quality/wpmvc-addon-license-key/assets/languages/'
        );
    }
}