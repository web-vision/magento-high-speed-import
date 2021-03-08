<?php

use Cemes\Pdo\Expression;

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
 */
class Fci_Model_Resource_Product extends Fci_Model_Resource_AbstractResource
{
    /**
     * @var string
     */
    public static $_confProductSku;

    /**
     * @var int
     */
    public static $_confProductId;

    /**
     * Fci_Model_Resource_Category constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTable = 'catalog_product_entity';
    }

    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $product)
    {
        /** @var Fci_Model_Product $product */
        // start time tracking
        Cemes_Registry::get('insert_timer')->start();

        if (!$product->getData('sku')
            || !$product->getData('name')
            || !$product->getData('name')->getValue()
            || !$product->getData('price')
            || !$product->getData('price')->getValue()
        ) {
            $missing = ['sku', 'name', 'price'];
            $mappings = $this->_config->getMappings();
            if ($mappings) {
                foreach ($missing as $id => $field) {
                    if (in_array($field, $mappings, true)) {
                        $missing[$id] = str_replace('_', ' ', array_search($field, $mappings, true));
                    }
                }
            }
            $context = [
                'sku' => $product->getData('sku'),
                'missing' => $missing,
            ];
            $this->_logger->error(
                'Produkt mit der SKU "{sku}" kann nicht importiert werden, da eines der folgenden Pflichtfelder leer ist. {missing}',
                $context
            );

            return $this;
        }

        $this->_beforeSave($product);

        $insert = $this->_mysql->insert($this->getEntityTable())
            ->set('attribute_set_id', $product->getData('attribute_set_id'))
            ->set('type_id', $product->getData('type_id'))
            ->set('sku', $product->getData('sku'))
            ->set('has_options', $product->getData('has_options'))
            ->set('created_at', new Expression('NOW()'))
            ->set('updated_at', new Expression('NOW()'));

        if ($this->_fieldExistsInTable($this->getEntityTable(), 'entity_type_id')) {
            $insert->set('entity_type_id', $product->getData('entity_type_id'));
        }

        $insert->query();

        $product->setId($this->_mysql->lastInsertId());

        $product->getAttributeResource()->insert($product);

        // ========== relation between configurable and simple product ==========
        // configurable products logic
        if ($product->getTypeId() === 'simple') { // simple
            $configurableProductId = null;
            if ($product->hasData('configurable_product_id')) {
                $configurableProductId = $product->getData('configurable_product_id');
            } elseif (strpos($product->getSku(), static::$_confProductSku) !== false) {
                $configurableProductId = static::$_confProductId;
            }

            if (null !== $configurableProductId) {
                $this->_mysql->insert('catalog_product_relation')
                    ->set('parent_id', $configurableProductId)
                    ->set('child_id', $product->getId())
                    ->execute();
                $this->_mysql->insert('catalog_product_super_link')
                    ->set('parent_id', $configurableProductId)
                    ->set('product_id', $product->getId())
                    ->execute();
            }
        } elseif ($product->getTypeId() === 'configurable') { // configurable
            // save sku and id globally to check if next simple product is a subproduct
            static::$_confProductSku = $product->getSku();
            static::$_confProductId = $product->getId();

            if (is_array($product->getData('product_options'))) {
                // save the attributes that are configurable
                $position = 0;
                foreach ($product->getData('product_options') as $optionLabel => $optionId) {
                    $this->_mysql->insert('catalog_product_super_attribute')
                        ->set('product_id', static::$_confProductId)
                        ->set('attribute_id', $optionId)
                        ->set('position', $position++)
                        ->execute();
                    $sid = $this->_mysql->select('catalog_product_super_attribute', ['product_super_attribute_id'])
                        ->where('product_id', static::$_confProductId)
                        ->where('attribute_id', $optionId)
                        ->fetchEntry('product_super_attribute_id');
                    $this->_mysql->insert('catalog_product_super_attribute_label')
                        ->set('product_super_attribute_id', $sid)
                        ->set('value', $optionLabel)
                        ->execute();
                }
            }
        }

        // ========== product links ==========
        $linkResource = new Fci_Model_Resource_Product_Link();
        $linkResource->processInsert($product);

        // ========== stock data ==========
        $repository = new Fci_Model_Resource_Product_Stock();
        $repository->insert($product);

        // ========== website product relation ==========
        $insert = $this->_mysql->insert('catalog_product_website')
            ->columns(['product_id', 'website_id']);
        foreach ($product->getData('websites') as $websiteId) {
            $insert->values([$product->getId(), $websiteId]);
        }
        $insert->execute();

        // ========== category product relation ==========
        $insert = $this->_mysql->insert('catalog_category_product')
            ->columns(['product_id', 'category_id', 'position']);
        foreach ($product->getData('category_ids') as $category) {
            $insert->values([$product->getId(), $category['id'], $category['position']]);
        }
        $insert->execute();

        // ========== import gallery ==========
        $repository = new Fci_Model_Resource_Product_Gallery();
        $repository->insert($product);

        // ========== import tier_price ==========
        if (count($product->getData('tier_price')) !== 0) {
            $insert = $this->_mysql->insert('catalog_product_entity_tier_price')
                ->columns(['entity_id', 'all_groups', 'customer_group_id', 'qty', 'value', 'website_id']);
            foreach ($product->getData('tier_price') as $tierPrice) {
                $insert->values(
                    [
                        $product->getId(),
                        0,
                        $tierPrice['customer_group'],
                        $tierPrice['qty'],
                        $tierPrice['price'],
                        $tierPrice['website'],
                    ]
                );
            }
            $insert->execute();
        }

        // ========== import group_price ==========
        if (count($product->getData('group_price')) !== 0) {
            $insert = $this->_mysql->insert('catalog_product_entity_group_price')
                ->columns(['entity_id', 'all_groups', 'customer_group_id', 'value', 'website_id']);
            foreach ($product->getData('group_price') as $groupPrice) {
                $insert->values(
                    [
                        $product->getId(),
                        0,
                        $groupPrice['customer_group'],
                        $groupPrice['price'],
                        $groupPrice['website'],
                    ]
                );
            }
            $insert->execute();
        }

        // ========== import simple product options ==========
        $repository = new Fci_Model_Resource_Product_Option();
        $repository->insert($product);

        $this->_afterSave($product);

        static::$_processedEntities++;
        static::$_insertedEntities++;

        $context = [
            'entitycount' => static::getProcessedEntities(),
            'sku'         => $product->getSku(),
        ];
        $this->_logger->success(
            'Produkt #{entitycount} mit der SKU "{sku}" erfolgreich eingetragen.',
            $context
        );

        Cemes_Registry::get('insert_timer')->stop();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $product)
    {
        /** @var Fci_Model_Product $product */
        // start time tracking
        Cemes_Registry::get('update_timer')->start();

        if (!$product->getData('sku')) {
            $this->_logger->error('Produkt kann nicht aktualisiert werden, da keine SKU existiert');

            return $this;
        }

        $this->_beforeSave($product);

        // ========== remove special price ==========
        if ($this->_config->getUnsetSpecialPrice() && !$product->hasData('special_price'))
        {
            $this->_mysql->delete('catalog_product_entity_decimal')
                ->where('attribute_id', $this->_cache->getData('productAttributes/special_price/attribute_id'))
                ->where('entity_id', $product->getId())
                ->execute();

            $this->_mysql->delete('catalog_product_entity_datetime')
                ->where('attribute_id', array(
                    $this->_cache->getData('productAttributes/special_from_date/attribute_id'),
                    $this->_cache->getData('productAttributes/special_to_date/attribute_id')), 'in')
                ->where('entity_id', $product->getId())
                ->execute();

            Cemes_Registry::get('logger')->notice('Special price without value in the CSV file is unset.');
        }

        // ========== catalog_product_entity ==========
        // updated_at date
        $this->_mysql->update($this->getEntityTable())
            ->set('updated_at', new Expression('NOW()'))
            ->where('entity_id', $product->getId())
            ->execute();

        // ========== relation between configurable and simple product ==========
        // configurable products logic
        if ($product->getTypeId() === 'simple') { // simple
            $configurableProductId = null;
            if ($product->hasData('configurable_product_id')) {
                $configurableProductId = $product->getData('configurable_product_id');
            } elseif (strpos($product->getSku(), static::$_confProductSku) !== false) {
                $configurableProductId = static::$_confProductId;
            }

            if (null !== $configurableProductId) {
                $this->_mysql->select('catalog_product_relation')
                    ->where('parent_id', $configurableProductId)
                    ->where('child_id', $product->getId())
                    ->query();
                if ($this->_mysql->count() === 0) {
                    $this->_mysql->insert('catalog_product_relation')
                        ->set('parent_id', $configurableProductId)
                        ->set('child_id', $product->getId())
                        ->execute();
                }
                $this->_mysql->select('catalog_product_super_link')
                    ->where('parent_id', $configurableProductId)
                    ->where('product_id', $product->getId())
                    ->query();
                if ($this->_mysql->count() === 0) {
                    $this->_mysql->insert('catalog_product_super_link')
                        ->set('parent_id', $configurableProductId)
                        ->set('product_id', $product->getId())
                        ->execute();
                }
            }
        } elseif ($product->getTypeId() === 'configurable') { // configurable
            // save sku and id globally to check if next simple product is a subproduct
            static::$_confProductSku = $product->getSku();
            static::$_confProductId = $product->getId();

            if (is_array($product->getData('product_options'))) {
                // save the attributes that are configurable
                foreach ($product->getData('product_options') as $optionLabel => $optionId) {
                    $this->_mysql->select('catalog_product_super_attribute')
                        ->where('product_id', static::$_confProductId)
                        ->where('attribute_id', $optionId)
                        ->query();
                    if ($this->_mysql->count() === 0) {
                        $this->_mysql->insert('catalog_product_super_attribute')
                            ->set('product_id', static::$_confProductId)
                            ->set('attribute_id', $optionId)
                            ->execute();
                        $sid = $this->_mysql->select('catalog_product_super_attribute', ['product_super_attribute_id'])
                            ->where('product_id', static::$_confProductId)
                            ->where('attribute_id', $optionId)
                            ->fetchEntry('product_super_attribute_id');
                        $this->_mysql->insert('catalog_product_super_attribute_label')
                            ->set('product_super_attribute_id', $sid)
                            ->set('value', $optionLabel)
                            ->execute();
                    }
                }
            }
        }

        // ========== create attribute data ==========
        $product->getAttributeResource()->update($product);

        // ========== product links ==========
        $linkResource = new Fci_Model_Resource_Product_Link();
        $linkResource->processUpdate($product);

        // ========== stock data ==========
        $repository = new Fci_Model_Resource_Product_Stock();
        $repository->update($product);

        // ========== website product relation ==========
        $delete = $this->_mysql->delete('catalog_product_website')
            ->where('product_id', $product->getId());
        if ($this->_config->getClearExistingWebsites() === '0') {
            // only delete entries we will add again in the next step
            $delete->where('website_id', $product->getData('websites'), 'in');
        }
        $delete->execute();

        $insert = $this->_mysql->insert('catalog_product_website')
            ->columns(['product_id', 'website_id']);
        foreach ($product->getData('websites') as $websiteId) {
            $insert->values([$product->getId(), $websiteId]);
        }
        $insert->execute();

        // ========== category product relation ==========
        if ($product->hasData('category_ids')) {
            $categoryDataArray = $product->getData('category_ids');
            $catIds = [];
            $catIdsWithPosition = [];
            $catIdsWithoutPosition = [];
            foreach ($categoryDataArray as $categoryData) {
                $catIds[] = $categoryData['id'];

                if ($categoryData['position'] !== $this->_config->getDefaultProductPosition()) {
                    $catIdsWithPosition[] = $categoryData;
                } else {
                    $catIdsWithoutPosition[] = $categoryData;
                }
            }

            // category product
            // delete all relations that are not going to be updated or inserted
            $delete = $this->_mysql->delete('catalog_category_product')
                ->where('product_id', $product->getId());
            if (!empty($catIds)) {
                $delete->where('category_id', $catIds, 'nin');
            }
            $delete->execute();

            // update or insert relations that have a specific position
            if (!empty($catIdsWithPosition)) {
                $replace = $this->_mysql->replace('catalog_category_product')
                    ->columns(['product_id', 'category_id', 'position']);
                foreach ($catIdsWithPosition as $category) {
                    $replace->values([$product->getId(), $category['id'], $category['position']]);
                }
                $replace->execute();
            }

            // insert relations that are not already present and have no specific position
            if (!empty($catIdsWithoutPosition)) {
                $entries = $this->_mysql->select('catalog_category_product', ['category_id'])
                    ->where('product_id', $product->getId())
                    ->where('category_id', $catIds, 'in')
                    ->fetchAll('category_id', PDO::FETCH_ASSOC);

                $replace = $this->_mysql->replace('catalog_category_product')
                    ->columns(['product_id', 'category_id', 'position']);
                $newEntriesPresent = false;
                foreach ($catIdsWithoutPosition as $category) {
                    if (!$entries || !in_array($category['id'], $entries, false)) {
                        $replace->values([$product->getId(), $category['id'], $category['position']]);
                        $newEntriesPresent = true;
                    }
                }

                if ($newEntriesPresent) {
                    $replace->execute();
                }
            }
        } elseif (in_array('categories', Fci_Model::$csvFields, true)
            || in_array('category_ids', Fci_Model::$csvFields, true)) {
            // category product
            $this->_mysql->delete('catalog_category_product')
                ->where('product_id', $product->getId())
                ->execute();
        }

        // ========== import gallery ==========
        $repository = new Fci_Model_Resource_Product_Gallery();
        $repository->update($product);

        // ========== import tier_price ==========
        if (count($product->getData('tier_price')) !== 0) {
            $this->_mysql->delete('catalog_product_entity_tier_price')
                ->where('entity_id', $product->getId())
                ->execute();
            $insert = $this->_mysql->insert('catalog_product_entity_tier_price')
                ->columns(['entity_id', 'all_groups', 'customer_group_id', 'qty', 'value', 'website_id']);
            foreach ($product->getData('tier_price') as $tierPrice) {
                $insert->values(
                    [
                        $product->getId(),
                        0,
                        $tierPrice['customer_group'],
                        $tierPrice['qty'],
                        $tierPrice['price'],
                        $tierPrice['website'],
                    ]
                );
            }
            $insert->execute();
        }

        // ========== import group_price ==========
        if (count($product->getData('group_price')) !== 0) {
            $this->_mysql->delete('catalog_product_entity_group_price')
                ->where('entity_id', $product->getId())
                ->execute();
            $insert = $this->_mysql->insert('catalog_product_entity_group_price')
                ->columns(['entity_id', 'all_groups', 'customer_group_id', 'value', 'website_id']);
            foreach ($product->getData('group_price') as $groupPrice) {
                $insert->values(
                    [
                        $product->getId(),
                        0,
                        $groupPrice['customer_group'],
                        $groupPrice['price'],
                        $groupPrice['website'],
                    ]
                );
            }
            $insert->execute();
        }

        // ========== import simple product options ==========
        if (count($product->getData('options')) !== 0) {
            $repository = new Fci_Model_Resource_Product_Option();
            $repository->update($product);
        }

        $this->_afterSave($product);

        static::$_processedEntities++;
        static::$_updatedEntities++;

        $context = [
            'entitycount' => static::getProcessedEntities(),
            'sku'         => $product->getSku(),
        ];
        $this->_logger->success(
            'Produkt #{entitycount} mit der SKU "{sku}" erfolgreich aktualisiert.',
            $context
        );

        Cemes_Registry::get('update_timer')->stop();

        return $this;
    }
}
