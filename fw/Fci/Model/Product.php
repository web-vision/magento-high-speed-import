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
 * @package     Fci_Model
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 * @method int getEntityTypeId()
 * @method int getAttributeSetId()
 * @method string getTypeId()
 * @method string getSku()
 * @method int getHasOptions()
 * @method int getRequiredOptions()
 */
class Fci_Model_Product extends Fci_Model_AbstractEntity
{
    /**
     * Fci_Model_Product constructor.
     *
     * @param array $entityId [optional] The entity id of the product if it already exists.
     */
    public function __construct($entityId = array())
    {
        parent::__construct();

        $cache = Fci_Objects_Cache::getInstance();

        $entityId = array_filter($entityId);

        if ($entityId) {
            $this->setData('entity_id', reset($entityId));
        } else {
            $this->setIsNew();
        }

        // set mandatory attributes for insert and update
        $this->setData('entity_type_id', $cache->getData('entityTypeIds/catalog_product'));

        if ($this->hasData('entity_id')) {
            return;
        }

        // set mandatory attributes for insert only
        $defaults = Fci_Helper_Config::getInstance()->getProductDefaults();
        $this->setData('attribute_set_id', Fci_Helper_Config::getInstance()->getProductDefault('attribute_set'));

        // values that have to come from the csv
        $forceCsvValue = array(
            'sku',
            'name',
            'price',
        );

        // set default values from config
        foreach ($defaults as $attributeCode => $value) {
            $attribute = $cache->getProductAttribute($attributeCode);
            if ($attribute->getBackendType() === 'static') {
                $this->setData($attributeCode, $value);
            } elseif (!$attribute->isEmpty()) {
                $attribute->setValue($value);
                $this->setData($attributeCode, $attribute);
            }
        }

        // set default values for required attributes
        foreach ($cache->getData('productAttributes') as $attributeCode => $attributeData) {
            if ($this->hasData($attributeCode)) {
                continue;
            }

            $attribute = $cache->getProductAttribute($attributeCode);

            if ($attribute->getIsRequired() && !in_array($attributeCode, $forceCsvValue, true)) {
                $value = null;
                if ($attributeData['default_value'] !== false) {
                    $value = $attributeData['default_value'];
                }

                if ($value === null) {
                    switch($attributeData['backend_type']) {
                        case 'int':
                            $value = 0;
                            break;
                        case 'decimal':
                            $value = 0.0;
                            break;
                        case 'varchar':
                        case 'text':
                        default:
                            $value = '';
                            break;
                    }
                }

                if ($attribute->getBackendType() === 'static') {
                    $this->setData($attributeCode, $value);
                } else {
                    $attribute->setValue($value);
                    $this->setData($attributeCode, $attribute);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getResource()
    {
        return new Fci_Model_Resource_Product();
    }

    /**
     * @inheritDoc
     */
    public function getAttributeResource()
    {
        return new Fci_Model_Resource_Attribute();
    }
}
