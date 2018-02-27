<?php
/**
 * Missing License key error notice.
 *
 * @see FullyLicensedBridge>add_hooks()
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.0
 */
?>
<div class="notice notice-error">
    <ul>
        <li>
            <span>
                <?= sprintf(
                    __( 'Please activate a valid license key for %s "<strong>%s</strong>".', 'addon'),
                    $main->config->get( 'type' ),
                    $main->config->get( 'license_api.name' )
                ) ?>
            </span>
            <span>
                <?php if ( $main->config->get( 'type' ) === 'plugin' ) : ?>
                    <a href="<?= admin_url( '/admin.php?page=addon-manage-license-key&ref='.strtolower( $main->config->get( 'namespace' ) ) ) ?>">
                        <?php _e( 'Activate License Key', 'addon' ) ?>
                    </a>
                <?php elseif ( $main->config->get( 'type' ) === 'theme' ) : ?>
                    <a href="<?= admin_url( '/themes.php?page=addon-manage-license-key&ref=theme' ) ?>">
                        <?php _e( 'Activate License Key', 'addon' ) ?>
                    </a>
                <?php endif ?>
            </span>
        </li>
    </ul>
</div>