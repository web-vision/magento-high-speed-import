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
abstract class Fci_Helper_Magento_AbstractMagento
{
    /**
     * Returns the config to connect to the database.
     *
     * @return []
     */
    abstract public function getDatabaseConfig();

    /**
     * Initializes magento.
     */
    abstract public function init();

    /**
     * Change translator to language for current store.
     *
     * @param string $storeCode
     */
    abstract public function setLocale($storeCode);

    /**
     * Sets the currently active store.
     *
     * @param string $storeCode
     */
    abstract public function setCurrentStore($storeCode);

    /**
     * Returns all options of the given attribute source model.
     *
     * @param string $attributeSourceModel
     *
     * @return []
     */
    abstract public function getAttributeOptions($attributeSourceModel);

    /**
     * Returns the table name in magento for the given internal table name.
     *
     * @param string $internalTableName
     *
     * @return string
     */
    abstract public function getTable($internalTableName);

    /**
     * Unloads magento.
     */
    abstract public function unload();

    /**
     * Post processing of an entity to modify it's content based on the magento version.
     *
     * @param Fci_Model_AbstractEntity $entity
     */
    abstract public function postProcessEntity(Fci_Model_AbstractEntity $entity);

    /**
     * Returns the string for the index table to show magento that the index needs to be rebuild.
     *
     * @return string
     */
    abstract public function getIndexState();

    /**
     * Returns the path to the media directory.
     *
     * @return string
     */
    abstract public function getMediaDirectory();
}
