<?php

namespace WPMVC\Addons\LicenseKey\Controllers;

use WP_Error;
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
            $url = $addon->get_config( 'updater.change_log_url' );
            if ( ! empty( $url ) )
                $update->set_url( $url );
            // Custom Icon
            $asset = $addon->get_config( 'updater.icon_asset' );
            if ( ! empty( $asset ) )
                $update->set_icon( assets_url( $asset, $addon->get_config( 'paths.base' ) ) );
        }
        return $update;
    }
    /**
     * Filters update info data to return full response data.
     * @since 2.0.0
     * 
     * @param \WPMVC\Addons\Updater\Models\UpdateData  $update
     * @param \WPMVC\Addons\LicenseKey\LicenseKeyAddon $addon
     * 
     * @return \WPMVC\Addons\Updater\Models\UpdateData
     */
    public function on_info( $update, $addon )
    {
        $license_key = $addon->get_license_key();
        if ( $license_key ) {
            if ( $addon->is_license_key_softvalid() ) {
                if ( $license_key->data && $license_key->data->downloadable ) {
                    // Update new downloadable
                    $update->set_version( $license_key->data->downloadable->name );
                    $update->set_package( $license_key->data->downloadable->url );
                }
                // Url / Change log
                $url = $addon->get_config( 'updater.change_log_url' );
                if ( ! empty( $url ) )
                    $update->set_url( $url );
                // Custom Icon
                $asset = $addon->get_config( 'updater.icon_asset' );
                if ( ! empty( $asset ) )
                    $update->set_icon( assets_url( $asset, $addon->get_config( 'paths.base' ) ) );
            } else {
                $errors = [];
                if ( isset( $license_key->data->errors ) )
                    foreach ( $license_key->data->errors as $key => $messages ) {
                        $errors = array_merge(
                            $errors,
                            array_map( function( $message ) { return __( $message, 'wpmvc-addon-license-key' ); }, $messages ) );
                    }
                return new WP_Error(
                    'plugins_api_failed',
                    implode( ' ' , $errors ),
                    isset( $license_key->data->errors ) ? $license_key->data->errors : []
                );
            }
        }
        return $update;
    }
}