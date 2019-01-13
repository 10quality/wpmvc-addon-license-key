<?php
/**
 * Extend notice.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.1.0
 */
?>
<div class="notice notice-warning">
    <p>
        <?= sprintf(
            __( 'Your license key for <strong>%s</strong> will expire soon (on "%s").', 'wpmvc-addon-license-key' ),
            $main->config->get( 'license_api.name' ),
            date( 'Y-m-d', strtotime( $license_key->data->expire_date ) )
        ) ?>
        &nbsp;<a href="<?= $extend_url ?>"><?php _e( 'Extend license', 'wpmvc-addon-license-key' ) ?></a>
    </p>
</div>