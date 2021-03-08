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
 * @package     Cemes\Log
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Log_CliLogger extends Fci_Log_AbstractLogger
{
    /**
     * @var bool
     */
    protected $_addTime = false;

    /**
     * @var string
     */
    protected $_timeFormat = 'Y-m-d H:i:s';

    /**
     * Sets if the time should be prefixed to the message.
     *
     * @param bool $value
     */
    public function addTime($value = true)
    {
        $this->_addTime = $value;
    }

    /**
     * Sets the time format to use if addTime is set to true.
     *
     * @param string $format
     */
    public function setTimeFormat($format = 'Y-m-d H:i:s')
    {
        $this->_timeFormat = $format;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        $string = $this->_interpolate($message, $context);
        $string = trim($string);

        $prefix = '';
        if ($this->_addTime) {
            $date = new \DateTime();
            $prefix .= '[' . $date->format($this->_timeFormat) . '] ';
        }
        $prefix .= strtoupper($level) . ': ';

        echo $prefix . $string . PHP_EOL;

        if ($level === \Psr\Log\LogLevel::CRITICAL) {
            Cemes::getInstance()->displayErrors();
            exit(1);
        }
    }
}
