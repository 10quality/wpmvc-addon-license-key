<?php
/**
 * Renew notice.
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
            __( 'Your license key for <strong>%s</strong> has expired.', 'wpmvc-addon-license-key' ),
            $main->config->get( 'license_api.name' )
        ) ?>
        &nbsp;<a href="<?= $renew_url ?>"><?php _e( 'Renew license', 'wpmvc-addon-license-key' ) ?></a>
    </p>
</div>