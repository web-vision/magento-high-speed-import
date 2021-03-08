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
class Fci_Helper_Factory_MagentoFactory
{
    /**
     * @var Fci_Helper_Magento_AbstractMagento
     */
    protected static $_magento;

    /**
     * Returns a magento helper for either Magento 1 or Magento 2 based on the existence of the Mage.php file.
     *
     * @return Fci_Helper_Magento_AbstractMagento
     */
    public static function getMagento()
    {
        if (static::$_magento) {
            return static::$_magento;
        }

        $magentoRoot = dirname(ROOT_BASEDIR);

        if (file_exists($magentoRoot . CDS . 'app' . CDS . 'Mage.php')) {
            static::$_magento = new Fci_Helper_Magento_Magento1();
        } else {
            static::$_magento = new Fci_Helper_Magento_Magento2();
        }

        return static::$_magento;
    }
}
