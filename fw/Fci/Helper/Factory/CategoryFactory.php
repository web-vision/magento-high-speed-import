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
class Fci_Helper_Factory_CategoryFactory extends Fci_Helper_Factory_AbstractFactory
{
    /**
     * @var bool
     */
    protected $_donNotProcessPath = false;

    /**
     * Creates a category with the given data and returns it.
     *
     * @param array $data
     *
     * @return Fci_Model_Category
     */
    public function createCategory($data)
    {
        $category = new Fci_Model_Category();

        $attrHelper = $this->_getAttributeHelper($category);

        foreach ($data as $attributeCode => $value) {
            $attribute = $this->_cache->getCategoryAttribute($attributeCode);
            if (!$attribute->isEmpty()) {
                $attrHelper->setAttribute($attribute);
                $value = $attrHelper->normalizeValue($value);

                // don't add value that couldn't be normalized
                if ($value === null) {
                    continue;
                }

                if ($attribute->getBackendType() === 'static') {
                    $category->setData($attributeCode, $value);
                } else {
                    $attribute->setValue($value);
                    $category->setData($attributeCode, $attribute);
                }
            }
        }

        if (!$this->_donNotProcessPath && $category->hasData('path')) {
            if (array_key_exists('root_category', $data)) {
                $category->setData('root_category', $data['root_category']);
            }
            $this->_processPath($category);
        }

        // attributes that are not in the eav_attribute table or get processed differently
        if ($category->hasData('name') && !$category->hasData('url_key')) {
            $urlKeyAttribute = $this->_cache->getCategoryAttribute('url_key');

            $urlKey = Fci_Helper_Entity::getUrlPath($category->getData('name')->getValue());
            $urlKeyAttribute->setValue($urlKey);
            $category->setData('url_key', $urlKeyAttribute);
        }

        $category->setData('stores', $this->_config->getStores($data));
        $category->setData('websites', $this->_config->getWebsites($data));

        return $category;
    }

    /**
     * Processes the path. If a category does not exists if will be created with just it's name.
     *
     * @param Fci_Model_Category $category
     */
    protected function _processPath(Fci_Model_Category $category)
    {
        $categories = $this->_cache->getData('categories');

        $parentCategories = explode($this->_config->getSubCategorySeparate(), $category->getPath());

        // last element is name of the category
        $name = array_pop($parentCategories);
        $name = trim($name);

        if (!$name) {
            $category->setData('path', null);
            Cemes_Registry::get('logger')->warning('Kategorie ohne Namen kann nicht importiert werden.');
            return;
        }

        if (!$category->hasData('name') || !$category->getData('name')->getValue()) {
            $nameAttribute = $this->_cache->getCategoryAttribute('name');
            $nameAttribute->setValue($name);
            $category->setData('name', $nameAttribute);
        }

        // rebuild path beginning at the root category
        if ($category->hasData('root_category')) {
            $parentId = $category->getData('root_category');
        } else {
            $parentId = $this->_config->getRootCategory();
        }
        $path = '1/' . $parentId;
        foreach ($parentCategories as $categoryPart) {
            $categoryName = explode(':', $categoryPart);
            $categoryName = trim(reset($categoryName));

            if (!$categoryName) {
                Cemes_Registry::get('logger')->warning('Unterkategorie ohne Namen wurde übersprungen.');
                continue;
            }

            if (array_key_exists($categoryName . ':' . $parentId, $categories)) {
                $parentCategory = $categories[$categoryName . ':' . $parentId];
                $parentId = $parentCategory['id'];
                $path .= '/' . $parentId;
            } elseif ($this->_config->createCategories()) {
                $this->_donNotProcessPath = true;
                $parentCategory = $this->createCategory(array('name' => $categoryName, 'path' => $path));
                $this->_donNotProcessPath = false;
                $parentCategory->save();
                // refresh variable with changed cache
                $categories = $this->_cache->getData('categories');
                $parentId = $parentCategory->getId();
                $path .= '/' . $parentId;
            } else {
                $category->setData('path', null);
                $context = array('name' => $categoryName);
                Cemes_Registry::get('logger')->warning(
                    'Kategorie mit Namen "{name}" ist unbekannt und anlegen von Kategorien ist deaktiviert.',
                    $context
                );
                return;
            }
        }

        // check if the category already exists
        if (array_key_exists($name . ':' . $parentId, $categories)) {
            $cachedCategory = $categories[$name . ':' . $parentId];
            $category->setData('entity_id', $cachedCategory['id']);
            $category->setIsNew(false);
            $path .= '/' . $cachedCategory['id'];
        } elseif (!$this->_config->createCategories()) {
            $path = null;
            $context = array('name' => $name);
            Cemes_Registry::get('logger')->warning(
                'Kategorie mit Namen "{name}" ist unbekannt und anlegen von Kategorien ist deaktiviert.',
                $context
            );
        }

        $category->setData('path', $path);
    }
}
