<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use WPMVC\Request;
use WPMVC\MVC\Controller;

/**
 * License key controller.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.0
 */
class SettingsController extends Controller
{
    /**
     * Returns action links.
     * @since 1.0.0
     *
     * @param array $links
     *
     * @return array
     */
    public function action_links( $links )
    {
        $ref = $this->main->config->get( 'type' ) === 'plugin'
            ? strtolower( $this->main->config->get( 'namespace' ) )
            : 'theme';
        $links[] = '<a href="'.admin_url( '/admin.php?page=addon-manage-license-key&ref='.$ref ).'">'
            .__( 'Manage License Key', 'addon' )
            .'</a>';
        return $links;
    }
    /**
     * Registers admin page.
     * @since 1.0.0
     *
     * @param array $links
     *
     * @return array
     */
    public function admin_menu( $links )
    {
        add_submenu_page(
            null,
            __( 'Manage License Key', 'license_keys' ),
            __( 'Manage License Key', 'license_keys' ),
            'manage_options',
            'addon-manage-license-key',
            [&$this, 'display_page']
        );
    }
    /**
     * Displays manage page.
     * @since 1.0.0
     *
     * @return array
     */
    public function display_page()
    {
        // Get global variable
        $ref = Request::input( 'ref', 'theme' );
        global $$ref;
        // Show
        $this->view->show('admin.manage-license', [
            'main'  => $$ref,
        ])
    }
}