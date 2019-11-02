<?php

namespace WPMVC\Addons\LicenseKey;

use WPMVC\Addon;
use WPMVC\Addons\LicenseKey\Utility\Encryption;

/**
 * Addon class.
 * Wordpress MVC.
 *
 * @link http://www.wordpress-mvc.com/v1/add-ons/
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 2.0.0
 */
class LicenseKeyAddon extends Addon
{
    /**
     * Constant tag.
     * @since 1.0.0
     *
     * @var string
     */
    const TAG = 'license-key';
    /**
     * Addon tag.
     * @since 1.0.0
     *
     * @var string
     */
    public $tag = 'license-key';
    /**
     * Function called everytime.
     * @since 2.0.0
     */
    public function init()
    {
        add_filter( 'wpmvc_update_data_' . $this->main->config->get( 'localize.textdomain' ), [&$this, 'on_update_check'] );
    }
    /**
     * Function called when user is on admin dashboard.
     * Add wordpress hooks (actions, filters) here.
     * @since 1.0.0
     * @since 1.0.4 Checks for updates.
     * @since 1.1.0 Added lang files.
     * @since 1.1.4 Fixes localization.
     */
    public function on_admin()
    {
        // Add action link for plugin
        if ( $this->main->config->get( 'paths.base_file' ) )
            add_filter(
                'plugin_action_links_'.$this->main->config->get( 'paths.base_file' ),
                [&$this, 'filter_action_links']
            );
        // Add manage page
        add_action( 'admin_menu', [&$this, 'admin_menu'], 99 );
        add_action( 'admin_notices', [&$this, 'notices'], 999 );
        // Localization
        add_action( 'init', [&$this, 'plugins_loaded'], 10 );
    }
    /**
     * Returns flag indicating if license key is valid.
     * @since 1.0.0
     * @since 1.0.6 Forced validation parameter.
     *
     * @param bool $force Flag that forces validation against the server.
     *
     * @return bool
     */
    public function is_license_key_valid( $force = false )
    {
        return $this->mvc->action( 'LicenseController@validate', $this->main, $force );
    }
    /**
     * Returns flag indicating if license key is valid (soft validation, NON SERVER VALIDATION).
     * @since 1.1.7
     *
     * @param bool $force Flag that forces validation against the server.
     *
     * @return bool
     */
    public function is_license_key_softvalid()
    {
        return $this->mvc->action( 'LicenseController@soft_validate', $this->main );
    }
    /**
     * Returns validation response.
     * @since 2.0.0
     *
     * @return object
     */
    public function check_license_key()
    {
        return $this->mvc->action( 'LicenseController@check', $this->main );
    }
    /**
     * Returns API response.
     * Tries to activate a license key.
     * @since 1.0.0
     *
     * @param string $license_key
     *
     * @return object
     */
    public function activate_license_key( $license_key )
    {
        return $this->mvc->action( 'LicenseController@activate',  $license_key, $this->main );
    }
    /**
     * Returns API response.
     * Tries to deactivate the activated license key.
     * @since 1.0.0
     *
     * @param string $license_key
     *
     * @return object
     */
    public function deactivate_license_key()
    {
        return $this->mvc->action( 'LicenseController@deactivate',  $this->main );
    }
    /**
     * Returns API response.
     * Tries to deactivate the activated license key.
     * @since 1.0.0
     *
     * @param string $license_key
     *
     * @return object
     */
    public function get_license_key()
    {
        return $this->mvc->action( 'LicenseController@get',  $this->main );
    }
    /**
     * Returns action links.
     * @since 1.0.0
     *
     * @param array $links
     *
     * @return array
     */
    public function filter_action_links( $links )
    {
        return $this->mvc->action( 'SettingsController@action_links', $links, $this->main );
    }
    /**
     * Action hook.
     * @since 1.0.0
     */
    public function admin_menu()
    {
        $this->mvc->call( 'SettingsController@admin_menu', $this->main );
    }
    /**
     * Displays missing license key notice.
     * @since 1.0.0
     */
    public function license_key_notice()
    {
        $this->mvc->view->show( 'admin.license-notice', ['main' => $this->main] );
    }
    /**
     * Filters update data to check if an update is available.
     * @since 1.2.0
     * 
     * @param \WPMVC\Addons\Updater\Models\UpdateData $update
     * 
     * @return \WPMVC\Addons\Updater\Models\UpdateData
     */
    public function on_update_check( $update )
    {
        return $this->mvc->action( 'UpdaterController@on_check', $update, $this );
    }
    /**
     * Action hook.
     * @since 1.0.4
     * @since 1.0.5 Fixes.
     * @since 1.0.7 Fixes main class parameter.
     * @since 1.0.11 Enable updates only if plugin is valid.
     * @since 1.1.0 Extension or renewal notices
     */
    public function notices()
    {
        // Check for renewal notices
        if ( $this->main->config->get( 'license_notices.enabled' ) ) {
            $params = [
                'main'          => $this->main,
                'license_key'   => $this->get_license_key(),
            ];
            if ( $params['license_key']
                && $params['license_key'] !== false
                && isset( $params['license_key']->data )
                && $params['license_key']->data->expire
            ) {
                // Check extension
                if ( $params['license_key'] === false || !isset( $params['license_key']->data->ctoken ) || $params['license_key']->data->ctoken === null )
                    return;
                if ( time() > $params['license_key']->data->expire
                    || $params['license_key']->data->has_expired
                    || $params['license_key']->data->status === 'inactive'
                ) {
                    // Renew?
                    $params['renew_url'] = $this->get_cart_url(
                        $params['license_key']->data->the_key,
                        $params['license_key']->data->ctoken,
                        'renew'
                    );
                    ob_start();
                    $this->main->view( 'admin.renew-notice', $params );
                    $view = ob_get_clean();
                    // Show notice
                    echo empty( $view )
                        ? $this->mvc->view->get( 'admin.renew-notice', $params )
                        : $view;
                } else if ( time() > strtotime( $this->main->config->get( 'license_notices.extend_interval' ), $params['license_key']->data->expire ) ) {
                    // Extend?
                    $params['extend_url'] = $this->get_cart_url(
                        $params['license_key']->data->the_key,
                        $params['license_key']->data->ctoken,
                        'extend'
                    );
                    ob_start();
                    $this->main->view( 'admin.extend-notice', $params );
                    $view = ob_get_clean();
                    // Show notice
                    echo empty( $view )
                        ? $this->mvc->view->get( 'admin.extend-notice', $params )
                        : $view;
                }
            }
        }
    }
    /**
     * Returns flag indicating if license string is valid.
     * @since 1.0.11
     *
     * @return bool
     */
    public function is_license_string_valid()
    {
        return $this->mvc->action( 'LicenseController@is_valid', $this->main );
    }
    /**
     * Validates license key. This validation is delayed until wordpress `init` action is ran.
     * Action "init".
     * @since 1.0.11
     */
    public function license_key_delayed_validation()
    {
        if ( !$this->main->is_valid )
            add_action( 'admin_notices', [&$this, 'license_key_notice'] );
    }
    /**
     * Loads text domain for localization.
     * Action "plugins_loaded"
     * @since 1.1.0
     */
    public function plugins_loaded()
    {
        $this->mvc->call( 'SettingsController@load_textdomain', $this->main );
    }
    /**
     * Returns a link to the cart for renewals or extensions.
     * @param 1.1.0
     * 
     * @param string $key    License Key code.
     * @param string $ctoken Token code.
     * @param string $action Cart action.
     * 
     * @return string
     */
    private function get_cart_url( $key, $ctoken, $action )
    {
        $cart_url = $this->main->config->get( 'license_notices.cart_url' );
        $cart_url .= strpos( $cart_url , '?' ) === false ? '?' : '&';
        return sprintf(
            '%slicense_key=%s&license_key_ctoken=%s&license_key_action=%s',
            $cart_url,
            $key,
            $ctoken,
            $action
        );
    }
}