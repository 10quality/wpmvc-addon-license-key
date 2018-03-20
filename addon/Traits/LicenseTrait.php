<?php

namespace WPMVC\Addons\LicenseKey\Traits;

/**
 * Trait for Bridge class (Main Class).
 * Enables bridge validation.
 *
 * @author Cami Mostajo
 * @package WPMVC\Addons\LicenseKey
 * @license MIT
 * @version 1.0.12
 */
trait LicenseTrait
{
    /**
     * Returns flag indicating if bridge is valid.
     * Valid bridge means that an activated license key is valid.
     * @since 1.0.0
     *
     * @var null|bool
     */
    protected static $is_valid;
    /**
     * Returns `is_valid` flag.
     * @since 1.0.12
     *
     * @return bool
     */
    protected function _is_valid()
    {
        return static::$is_valid;
    }
    /**
     * Returns updated `is_valid` flag.
     * @since 1.0.12
     *
     * @param bool $valid Flag to update.
     *
     * @return bool
     */
    protected function _set_is_valid( $valid )
    {
        static::$is_valid = $valid;
        return static::$is_valid;
    }
    /**
     * Returns flag indicating if `is_valid` flag is set.
     * @since 1.0.12
     *
     * @return bool
     */
    protected function _isset_is_valid()
    {
        return isset( static::$is_valid );
    }
}