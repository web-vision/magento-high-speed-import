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
 * @package     Fci_Objects
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
abstract class Fci_Objects_AbstractScript
{
    /**
     * @var string
     */
    protected $_eventName;

    abstract public function __construct($params);

    /**
     * Executes the script.
     *
     * @throws \Fci_Exceptions_ScriptException
     * @throws \Fci_Exceptions_CriticalScriptException
     */
    abstract public function execute();

    /**
     * Returns the event name.
     *
     * @return bool
     */
    public function getEventName()
    {
        return $this->_eventName;
    }

    /**
     * Returns an array with all params of the script and the current values.
     *
     * @return array
     */
    abstract public function getConfiguration();

    /**
     * Retrieves a value from the given params. If the value is not present or an empty string the default value will be
     * given.
     *
     * @param array  $params
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function _getValue($params, $key, $default = null)
    {
        return array_key_exists($key, $params) && $params[$key] !== '' ? $params[$key] : $default;
    }
}
