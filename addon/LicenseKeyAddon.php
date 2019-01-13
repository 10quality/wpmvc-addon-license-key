<?php

namespace WPMVC\Addons\LicenseKey;

use WPMVC\Addon;

/**
 * Addon class.
 * Wordpress MVC.
 *
 * @link http://www.wordpress-mvc.com/v1/add-ons/
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.1.0
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
     * Function called when user is on admin dashboard.
     * Add wordpress hooks (actions, filters) here.
     * @since 1.0.0
     * @since 1.0.4 Checks for updates.
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
     * Action hook.
     * @since 1.0.4
     * @since 1.0.5 Fixes.
     * @since 1.0.7 Fixes main class parameter.
     * @since 1.0.11 Enable updates only if plugin is valid.
     * @since 1.1.0 Extension or renewal notices
     */
    public function notices()
    {
        if ( $this->main->is_valid ) {
            $is_updated = get_option(
                $this->main->config->get( 'updater.option' ),
                true
            );
            // Update available
            if ( ! $is_updated ) {
                // Display notice
                $params = [
                    'main'          => $this->main,
                    'license_key'   => $this->get_license_key(),
                ];
                ob_start();
                $this->main->view( 'admin.update-notice', $params );
                $view = ob_get_clean();
                // Show notice
                echo empty( $view )
                    ? $this->mvc->view->get( 'admin.update-notice', $params )
                    : $view;
            }
        }
        // Check for renewal notices
        if ( $this->main->config->get( 'license_notices.enabled' ) ) {
            $params = [
                'main'          => $this->main,
                'license_key'   => $this->get_license_key(),
            ];
            if ( !isset( $params['license_key']->data->ctoken ) || $params['license_key']->data->ctoken === null )
                return;
            $has_notified = get_option(
                $this->main->config->get( 'license_notices.option' ),
                0,
                true //autoload
            );
            // Renew?
            if ( time() > $params['license_key']->data->expire ) {
                $params['renew_url'] = sprintf(
                    '%s?license_key=%s&license_key_ctoken=%s&license_key_action=renew'
                    $this->main->config->get( 'license_notices.cart_url' ),
                    $params['license_key']->data->the_key,
                    $params['license_key']->data->ctoken
                );
                ob_start();
                $this->main->view( 'admin.renew-notice', $params );
                $view = ob_get_clean();
                // Show notice
                echo empty( $view )
                    ? $this->mvc->view->get( 'admin.renew-notice', $params )
                    : $view;
            } else if ( !$has_notified
                && time() > strtotime( $this->main->config->get( 'license_notices.extend_interval' ), $params['license_key']->data->expire ) 
            ) {
                $params['extend_url'] = sprintf(
                    '%s?license_key=%s&license_key_ctoken=%s&license_key_action=extend'
                    $this->main->config->get( 'license_notices.cart_url' ),
                    $params['license_key']->data->the_key,
                    $params['license_key']->data->ctoken
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
}