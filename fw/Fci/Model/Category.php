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
 * @method int getParentId()
 * @method int getPath()
 * @method int getPosition()
 * @method int getLevel()
 * @method int getChildrenCount()
 */
class Fci_Model_Category extends Fci_Model_AbstractEntity
{
    /**
     * Fci_Model_Category constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $cache = Fci_Objects_Cache::getInstance();

        $this->setIsNew();
        $this->setData('attribute_set_id', $cache->getData('categoryAttributeSet'));
        $this->setData('entity_type_id', $cache->getData('entityTypeIds/catalog_category'));

        if ($this->hasData('entity_id')) {
            return;
        }

        $defaults = Fci_Helper_Config::getInstance()->getCategoryDefaults();

        // set default values from config
        foreach ($defaults as $attributeCode => $value) {
            $attribute = $cache->getCategoryAttribute($attributeCode);
            if ($attribute->getBackendType() === 'static' || $attribute->isEmpty()) {
                $this->setData($attributeCode, $value);
            } else {
                $attribute->setValue($value);
                $this->setData($attributeCode, $attribute);
            }
        }

        // set default values for required attributes
        foreach ($cache->getData('categoryAttributes') as $attributeCode => $attributeData) {
            if ($this->hasData($attributeCode)) {
                continue;
            }

            $attribute = $cache->getCategoryAttribute($attributeCode);

            if ($attribute->getIsRequired()) {
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
        return new Fci_Model_Resource_Category();
    }

    /**
     * @inheritDoc
     */
    public function getAttributeResource()
    {
        return new Fci_Model_Resource_Attribute();
    }
}
