<?php
/**
 * Not found page.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.1.0
 */
?>
<div class="wrap addon-license-key not-found">
    <h1 class="wp-heading-inline"><?php _e( 'Not found', 'wpmvc-addon-license-key' ) ?></h1>
    <p>
        <?= sprintf(
            __( 'No license key service found for plugin or theme with reference "<strong>%s</strong>".', 'wpmvc-addon-license-key' ),
            $ref
        ) ?>
    </p>
    <?php do_action( 'addon_license_key_after_not_found_page' ) ?>
</div><!--wrap-->