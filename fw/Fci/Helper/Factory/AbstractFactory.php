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
abstract class Fci_Helper_Factory_AbstractFactory
{
    /**
     * @var Fci_Helper_Attribute
     */
    protected $_attributeHelper;

    /**
     * @var Fci_Objects_Cache
     */
    protected $_cache;

    /**
     * @var Fci_Helper_Config
     */
    protected $_config;

    /**
     * Fci_Helper_Factory_AbstractFactory constructor.
     */
    public function __construct()
    {
        $this->_cache = Fci_Objects_Cache::getInstance();
        $this->_config = Fci_Helper_Config::getInstance();
    }

    /**
     * Returns the attribute helper. If the helper is not initialized yet it will be initialized with the config.
     * The store views of the given entity will be set each time on the attribute helper.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return Fci_Helper_Attribute
     */
    protected function _getAttributeHelper(Fci_Model_AbstractEntity $entity)
    {
        if (!$this->_attributeHelper) {
            $this->_attributeHelper = Fci_Helper_Attribute::getInstance();
            if ($entity instanceof Fci_Model_Product) {
                $this->_attributeHelper->setDefaults($this->_config->getProductDefaults());
            } elseif ($entity instanceof Fci_Model_Category) {
                $this->_attributeHelper->setDefaults($this->_config->getCategoryDefaults());
            }
            $this->_attributeHelper->setImportGlobally($this->_config->getImportGlobally());
            $this->_attributeHelper->setDateTimeFormat($this->_config->getDateTimeFormat());
        }

        $storeViews = $this->_attributeHelper->getImportGlobally() ? array(0) : $this->_config->getStores($entity);
        $this->_attributeHelper->setAttributeStoreViews($storeViews);

        return $this->_attributeHelper;
    }
}
