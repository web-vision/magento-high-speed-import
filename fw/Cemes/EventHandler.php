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
final class Cemes_EventHandler
{
    /**
     * @var Cemes_EventHandler
     */
    private static $instance;

    /**
     * @var callable[]
     */
    private $_events = array();

    /**
     * Cemes_EventHandler constructor.
     */
    private function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning the object.
     */
    private function __clone()
    {
    }

    /**
     * Singleton method to return just one instance of the class.
     *
     * @return Cemes_EventHandler
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new Cemes_EventHandler();
        }

        return static::$instance;
    }

    /**
     * Registers a handler for a specific event.
     *
     * @param string       $eventName
     * @param string|array $handler
     *
     * @throws InvalidArgumentException
     */
    public function registerForEvent($eventName, $handler)
    {
        if (!is_string($eventName)) {
            throw new InvalidArgumentException('Event name has to be a string.');
        }

        if (!is_callable($handler)) {
            throw new InvalidArgumentException('Handler must be callable.');
        }

        if (!array_key_exists($eventName, $this->_events)) {
            $this->_events[$eventName] = array();
        }

        $this->_events[$eventName][] = $handler;
    }

    /**
     * Dispatches an event.
     *
     * @param string $eventName
     * @param array  $args
     *
     * @throws Exception
     */
    public function dispatchEvent($eventName, array $args = array())
    {
        if (!array_key_exists($eventName, $this->_events)) {
            return;
        }

        $event = new Cemes_Object($args);

        foreach ($this->_events[$eventName] as $handler) {
            try {
                call_user_func($handler, $event);
                if ($event->getData('stop_propagation')) {
                    break;
                }
            } catch (Exception $e) {
                throw new Exception('Error during event execution.', $e->getCode(), $e);
            }
        }
    }
}
