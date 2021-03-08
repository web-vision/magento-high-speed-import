<?php

use Psr\Log\LogLevel;

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
 * @package     Fci_Log
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Log_HtmlLogger extends Fci_Log_AbstractLogger
{
    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        echo '<div class="CemesMsg_' . $level . '">' . $this->_interpolate($message, $context) . '</div>' . PHP_EOL;

        if ($level === \Psr\Log\LogLevel::CRITICAL) {
            Cemes::getInstance()->displayErrors();
            exit(1);
        }
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    protected function _interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            } elseif (is_array($val)) {
                $replace['{' . $key . '}'] = '<ul><li>' . implode('</li><li>', $val) . '</li></ul>';
            }
        }

        // interpolate replacement values into the message
        $string = strtr($message, $replace);

        return str_replace(PHP_EOL, '<br />', $string);
    }
}
