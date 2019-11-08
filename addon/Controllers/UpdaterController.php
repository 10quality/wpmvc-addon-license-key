<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use WPMVC\MVC\Controller;

/**
 * License key controller.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 2.0.0
 */
class UpdaterController extends Controller
{
    /**
     * Returns update data and checks if an update is available.
     * @since 2.0.0
     * 
     * @param \WPMVC\Addons\Updater\Models\UpdateData  $update
     * @param \WPMVC\Addons\LicenseKey\LicenseKeyAddon $addon
     * 
     * @return \WPMVC\Addons\Updater\Models\UpdateData
     */
    public function on_check( $update, $addon )
    {
        if ( $addon->is_license_key_softvalid() ) {
            // Update license and get new response
            $response = $addon->check_license_key();
            if ( ! $response->error && $response->data && $response->data->downloadable ) {
                // Update new downloadable
                $update->set_version( $response->data->downloadable->name );
                $update->set_package( $response->data->downloadable->url );
            }
            // Url / Change log
            $url = $addon->main->config->get( 'updater.change_log_url' );
            if ( ! empty( $url ) )
                $update->set_url( $url );
        }
        return $update;
    }
}