<?php

namespace Fci\View\Service;

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
 * @package     Fci\View\Controller
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class CacheFormatService
{
    /**
     * Returns all product attributes and additionally attribute set and type id.
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $cache = \Fci_Objects_Cache::getInstance();

        $productAttributes = $cache->getData('productAttributes');
        $this->_addAttributeSet($productAttributes);
        $this->_addTypeIds($productAttributes);

        return $productAttributes;
    }

    /**
     * Returns all category attributes and additionally the root categories.
     *
     * @return array
     */
    public function getCategoryAttributes()
    {
        $cache = \Fci_Objects_Cache::getInstance();

        $categoryAttributes = $cache->getData('categoryAttributes');
        $this->_addRootCategories($categoryAttributes);

        return $categoryAttributes;
    }

    /**
     * Adds all product attribute sets as values for the pseudo attribute 'attribute_set'.
     *
     * @param array $productAttributes
     */
    protected function _addAttributeSet(&$productAttributes)
    {
        $cache = \Fci_Objects_Cache::getInstance();

        $values = [];
        foreach ($cache->getData('productAttributeSets') as $id => $label) {
            $values[$id] = [$label];
        }

        $attributeSet = [
            'label'          => 'Attribute Set',
            'frontend_input' => 'select',
            'source_model'   => null,
            'values'         => $values,
        ];

        $productAttributes['attribute_set'] = $attributeSet;
    }

    /**
     * Adds all supported type ids as values for the pseudo attribute 'type_id'.
     *
     * @param array $productAttributes
     */
    protected function _addTypeIds(&$productAttributes)
    {
        $typeIds = [
            'label'          => 'Type Id',
            'frontend_input' => 'select',
            'source_model'   => null,
            'values'         => [
                'simple'       => ['Simple Product'],
                'configurable' => ['Configurable Product'],
            ],
        ];

        $productAttributes['type_id'] = $typeIds;
    }

    /**
     * Adds all root categories as values for the pseudo attribute 'root_category'.
     *
     * @param array $categoryAttributes
     */
    protected function _addRootCategories(&$categoryAttributes)
    {
        $cache = \Fci_Objects_Cache::getInstance();

        $values = $cache->getData('categories');
        $values = array_filter($values, function($value) {
            return $value['level'] === 1;
        });

        $categories = [];
        foreach ($values as $value) {
            $categories[$value['id']] = [$value['name']];
        }

        $rootCategory = [
            'label'          => 'Root Category',
            'frontend_input' => 'select',
            'source_model'   => null,
            'values'         => $categories,
        ];

        $categoryAttributes['root_category'] = $rootCategory;
    }
}
