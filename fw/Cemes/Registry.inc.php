<?php

/**
 * web-vision GmbH
 *
 * NOTICE OF LICENSE
 *
 * <!--LICENSETEXT-->
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.web-vision.de for more information.
 *
 * @category    WebVision
 * @package     Cemes
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Cemes_Registry {
    /**
     * @var array
     */
    protected static $data = array();

    private function __construct() {}
    private function __clone() {}

    /**
     * Register a value with a specific key.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function register($key, $value)
    {
        static::$data[$key] = $value;
    }

    /**
     * Gets a value from the registry with the specified key.
     * If the key doesn't exists the default value will be returned.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (array_key_exists($key, static::$data)) {
            return static::$data[$key];
        }

        return $default;
    }

    /**
     * Removes the value with the given key from the registry.
     *
     * @param string $key
     */
    public static function unregister($key)
    {
        if (array_key_exists($key, static::$data)) {
            unset(static::$data[$key]);
        }
    }
}
