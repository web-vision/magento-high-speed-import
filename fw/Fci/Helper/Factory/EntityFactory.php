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
class Fci_Helper_Factory_EntityFactory
{
    /**
     * @var Fci_Helper_Factory_CategoryFactory
     */
    protected static $_categoryFactory;

    /**
     * @var Fci_Helper_Factory_ProductFactory
     */
    protected static $_productFactory;

    /**
     * Depending on the current mode in the config either a category or product will be created with the given data.
     *
     * @param array $data
     * @param int $entityId
     *
     * @return Fci_Model_AbstractEntity
     */
    public static function createEntity($data, $entityId)
    {
        switch(Fci_Helper_Config::getInstance()->getMode()) {
            case 'categories':
                return static::createCategory($data);
            case 'products':
                return static::createProduct($data, $entityId);
        }

        return null;
    }

    /**
     * Creates a category with the given data.
     *
     * @param $data
     *
     * @return Fci_Model_Category
     */
    public static function createCategory($data)
    {
        return static::_getCategoryFactory()->createCategory($data);
    }

    /**
     * Creates a product with the given data.
     *
     * @param $data
     * @param $entityId
     *
     * @return Fci_Model_AbstractEntity
     */
    public static function createProduct($data, $entityId)
    {
        return static::_getProductFactory()->createProduct($data, $entityId);
    }

    /**
     * Returns a category factory.
     *
     * @return Fci_Helper_Factory_CategoryFactory
     */
    protected static function _getCategoryFactory()
    {
        if (!static::$_categoryFactory) {
            static::$_categoryFactory = new Fci_Helper_Factory_CategoryFactory();
        }

        return static::$_categoryFactory;
    }

    /**
     * Returns a product factory.
     *
     * @return Fci_Helper_Factory_ProductFactory
     */
    protected static function _getProductFactory()
    {
        if (!static::$_productFactory) {
            static::$_productFactory = new Fci_Helper_Factory_ProductFactory();
        }

        return static::$_productFactory;
    }
}
