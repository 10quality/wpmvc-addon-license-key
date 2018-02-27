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
 * @version 1.0.0
 */
class Addon extends Addon
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
     */
    public function on_admin()
    {
        add_filter(
            'plugin_action_links_'.$this->main->config->get('paths.base_file'),
            [&$this, 'filter_action_links']
        );
        add_action('admin_menu', [&$this, 'admin_menu'], 99);
    }
    /**
     * Returns flag indicating if license key is valid.
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_license_key_valid()
    {
        return $this->mvc->call( 'LicenseController@validate' );
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
        return $this->mvc->action( 'SettingsController@action_links', $links );
    }
    /**
     * Action hook.
     * @since 1.0.0
     */
    public function admin_menu()
    {
        $this->mvc->call( 'SettingsController@admin_menu' );
    }
}