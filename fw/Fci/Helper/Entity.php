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
 * @package     Fci_Helper
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Helper_Entity
{
    /**
     * Normalizes the given name to be used as an url path.
     *
     * @param string $name
     *
     * @return string
     */
    public static function getUrlPath($name)
    {
        $search = array('/ä/', '/ö/', '/ü/', '/ß/', '/\+/', '/[^A-Za-z0-9]/', '/[-][-]*/', '/[-]$/');
        $replace = array('ae', 'oe', 'ue', 'ss', 'plus', '-', '-', '');

        return strtolower(preg_replace($search, $replace, $name));
    }
}
