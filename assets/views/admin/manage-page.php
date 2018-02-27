<?php
/**
 * License key manage page.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.0
 */
?>
<style type="text/css">
.panel {
    background-color: #fff;
    border: 1px solid #eee;
    padding: 20px;
    margin: 20px 0;
}
table.short_table {
    width: 100%;
}
table.short_table th {
    text-align: left;
}
table.short_table input {
    width: 100%;
    font-size: 20px;
}
.actions {
    overflow: hidden;
    position: relative;
    margin-top: 10px;
}
.actions button {
    float: right;
}
.actions button.remove {
    float: right;
    color: #fff;
    border-color: #ff3838;
    background: #F44336;
    box-shadow: 0 1px 0 #ab9595;
}
.actions button.remove:hover {
    background: #E53935;
    border-color: #b37b7b;
    color: #fff1f0;
}
code.the-key {
    width: 100%;
    padding: 6px 0;
    margin-bottom: 10px;
    color: #00008b;
    font-size: 20px;
}
span.status-valid {
    color: #4CAF50;
    font-weight: 600;
}
span.status-invalid {
    color: #F44336;
    font-weight: 600;
}
</style>
<div class="wrap addon-license-key <?= $ref ?>-license-key">
    <h1 class="wp-heading-inline">
        <?= sprintf(
            __( 'Manage License Key for %s <strong>%s</strong>', 'addon' ),
            __( $main->config->get('type'), 'addon' ),
            $main->config->get('license_api.name')
        ) ?>
    </h1>
    <?php do_action( 'admin_notices' ) ?>
    <?php if ( count( $errors ) > 0 ) : ?>
        <div class="notices">
            <?php foreach ( $errors as $key => $messages ) : ?>
                <div class="notice notice-error <?= $key ?>">
                    <ul>
                        <?php foreach ( $messages as $message ) : ?>
                            <li><?= $message ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
    <?php if ( !empty( $response ) && isset( $response->message ) ) : ?>
        <div class="notices">
            <div class="notice notice-success">
                <ul>
                    <li><?= $response->message ?></li>
                </ul>
            </div>
        </div>
    <?php endif ?>
    <div class="panel">
        <?php if ( $license ) : ?>
            <h2><?php _e( 'License Key Activated', 'addon' ) ?></h2>
            <table class="short_table">
                <tbody>
                    <tr>
                        <th><?php _e( 'License Key Code', 'addon' ) ?></th>
                        <td><code class="the-key"><?= $license->data->the_key ?></code></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Activation ID', 'addon' ) ?></th>
                        <td>
                            <?= $license->data->activation_id ?>
                            <?php if ( $license->data->activation_id === 404 ) : ?>
                                <span> <?php _e( '(development activation)', 'addon' ) ?></span>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php if ( $license->data->activation_id !== 404 ) : ?>
                        <tr>
                            <th><?php _e( 'Activation date', 'addon' ) ?></th>
                            <td><?= date( get_option( 'date_format' ), $license->data->activation_id ) ?></td>
                        </tr>
                    <?php endif ?>
                    <?php if ( $license->data->expire ) : ?>
                        <tr>
                            <th><?php _e( 'Expires', 'addon' ) ?></th>
                            <td><?= date( get_option( 'date_format' ), $license->data->expire ) ?></td>
                        </tr>
                    <?php endif ?>
                    <tr>
                        <th><?php _e( 'Status', 'addon' ) ?></th>
                        <td>
                            <?php if ( $main->is_valid ) : ?>
                                <span class="status-valid"><?php _e( 'Valid activation.', 'addon' ) ?></span>
                            <?php else : ?>
                                <span class="status-invalid"><?php _e( 'Activation no longer valid.', 'addon' ) ?></span>
                            <?php endif ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <form method="POST">
                <div class="actions">
                    <button class="button remove"
                        type="submit"
                        name="action"
                        value="deactivate"
                    >
                        <?php _e( 'Deactivate', 'addon' ) ?>
                    </button>
                </div>
            </form>
        <?php else : ?>
            <h2><?php _e( 'Activate your License Key', 'addon' ) ?></h2>
            <form method="POST">
                <table class="short_table">
                    <tbody>
                        <tr>
                            <th><?php _e( 'License Key Code', 'addon' ) ?></th>
                            <td>
                                <input name="license_key"
                                    class="input"
                                    type="text"
                                    placeholder="<?php _e( 'XXXXXXXXXXXXXXXXXXXXXXXXX-XXX', 'addon' ) ?>"
                                    value="<?= $license_key ?>"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="actions">
                    <button class="button button-primary"
                        type="submit"
                        name="action"
                        value="activate"
                    >
                        <?php _e( 'Activate', 'addon' ) ?>
                    </button>
                </div>
            </form>
        <?php endif ?>
    </div><!--.panel-->
    <?php do_action( 'addon_license_key_after_manage_page_' . $ref ) ?>
</div><!--wrap-->