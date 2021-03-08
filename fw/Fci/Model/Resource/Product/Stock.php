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
 * WVTODO use_config_manage_stock; config abfragen wenn manage_stock = 1 dann use_config = 1 ansonsten 0
 */
class Fci_Model_Resource_Product_Stock extends Fci_Model_Resource_AbstractResource
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTable = 'cataloginventory_stock';
    }

    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $product)
    {
        if (!$product->hasData('stock_item')) {
            return;
        }

        /** @var Fci_Model_Product_StockItem $stockItem */
        $stockItem = $product->getData('stock_item');

        $this->_beforeSave($product);

        // stock item table
        $insert = $this->_mysql->insert($this->getEntityTable('item'))
            ->set('product_id', $product->getId())
            ->set('stock_id', '1')
            ->set('stock_status_changed_auto', '1');
        if ($stockItem->getData('qty') !== null) {
            $insert->set('qty', $stockItem->getData('qty'))
                ->set('manage_stock', $stockItem->getData('magage_stock'))
                ->set('use_config_manage_stock', $stockItem->getData('magage_stock') ? 0 : 1)
                ->set('is_in_stock', $stockItem->getData('is_in_stock'));
        }
        if ($stockItem->getData('min_sale_qty') !== null) {
            $insert->set('min_sale_qty', $stockItem->getData('min_sale_qty'))->set('use_config_min_sale_qty', 0);
        }
        if ($stockItem->getData('max_sale_qty') !== null) {
            $insert->set('max_sale_qty', $stockItem->getData('max_sale_qty'))->set('use_config_max_sale_qty', 0);
        }
        if ($stockItem->getData('is_qty_decimal') !== null) {
            $insert->set('is_qty_decimal', $stockItem->getData('is_qty_decimal'));
        }
        if ($stockItem->getData('backorders') !== null) {
            $insert->set('backorders', $stockItem->getData('backorders'))->set('use_config_backorders', 0);
        }
        if ($stockItem->getData('qty_increments') !== null && $stockItem->getData('qty_increments') > 0) {
            $insert->set('use_config_enable_qty_inc', 0)
                ->set('enable_qty_increments', 1)
                ->set('use_config_qty_increments', 0)
                ->set('qty_increments', $stockItem->getData('qty_increments'));
        }
        $insert->execute();

        // stock status table
        $insert = $this->_mysql->insert($this->getEntityTable('status'));
        $data = [];
        if ($stockItem->getData('qty') !== null) {
            $data['qty'] = $stockItem->getData('qty');
            $data['stock_status'] = $stockItem->getData('is_in_stock');
            $insert->columns(['product_id', 'website_id', 'stock_id', 'qty', 'stock_status']);
        } else {
            $insert->columns(['product_id', 'website_id', 'stock_id', 'stock_status']);
        }
        foreach ($product->getData('websites') as $websiteId) {
            if (count($data)) {
                $insert->values([$product->getId(), $websiteId, 1, $data['qty'], $data['stock_status']]);
            } else {
                $insert->values([$product->getId(), $websiteId, 1, 1]);
            }
        }
        $insert->execute();

        $this->_afterSave($product);
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $product)
    {
        if (!$product->hasData('stock_item')) {
            return;
        }

        /** @var Fci_Model_Product_StockItem $stockItem */
        $stockItem = $product->getData('stock_item');

        if ($stockItem->isEmpty()) {
            return;
        }

        $this->_beforeSave($product);

        // stock item table
        $this->_mysql->select($this->getEntityTable('item'))
            ->where('product_id', $product->getId())
            ->query();
        if ($this->_mysql->count() === 0) {
            $query = $this->_mysql->insert($this->getEntityTable('item'))
                ->set('product_id', $product->getId())
                ->set('stock_id', '1')
                ->set('stock_status_changed_auto', '1');
        } else {
            $itemId = $this->_mysql->fetchEntry(null, 'item_id');
            $query = $this->_mysql->update($this->getEntityTable('item'));
            $query->where('item_id', $itemId);
        }
        if ($stockItem->getData('qty') !== null) {
            $query->set('qty', $stockItem->getData('qty'))
                ->set('manage_stock', $stockItem->getData('magage_stock'))
                ->set('use_config_manage_stock', $stockItem->getData('magage_stock') ? 0 : 1)
                ->set('is_in_stock', $stockItem->getData('is_in_stock'));
        }
        if ($stockItem->getData('min_sale_qty') !== null) {
            $query->set('min_sale_qty', $stockItem->getData('min_sale_qty'))->set('use_config_min_sale_qty', 0);
        }
        if ($stockItem->getData('max_sale_qty') !== null) {
            $query->set('max_sale_qty', $stockItem->getData('max_sale_qty'))->set('use_config_max_sale_qty', 0);
        }
        if ($stockItem->getData('is_qty_decimal') !== null) {
            $query->set('is_qty_decimal', $stockItem->getData('is_qty_decimal'));
        }
        if ($stockItem->getData('backorders') !== null) {
            $query->set('backorders', $stockItem->getData('backorders'))->set('use_config_backorders', 0);
        }
        if ($stockItem->getData('qty_increments') !== null && $stockItem->getData('qty_increments') > 0) {
            $query->set('use_config_enable_qty_inc', 0)
                ->set('enable_qty_increments', 1)
                ->set('use_config_qty_increments', 0)
                ->set('qty_increments', $stockItem->getData('qty_increments'));
        }
        $query->execute();

        // stock status table
        $select = $this->_mysql->select($this->getEntityTable('status'))
            ->where('product_id', $product->getId());
        $i = 0;
        foreach ($product->getData('websites') as $websiteId) {
            if ($i++ === 0) {
                $select->where('website_id', $websiteId, 'eq', 1);
            } else {
                $select->orWhere('website_id', $websiteId, 'eq', 1);
            }
        }
        $select->query();

        if ($this->_mysql->count() === 0) {
            $query = $this->_mysql->insert($this->getEntityTable('status'));
        } else {
            $query = $this->_mysql->replace($this->getEntityTable('status'));
        }
        $data = [];
        if ($stockItem->getData('qty') !== null) {
            $data['qty'] = $stockItem->getData('qty');
            $data['stock_status'] = $stockItem->getData('is_in_stock');
            $query->columns(['product_id', 'website_id', 'stock_id', 'qty', 'stock_status']);
        } else {
            $query->columns(['product_id', 'website_id', 'stock_id', 'stock_status']);
        }
        foreach ($product->getData('websites') as $websiteId) {
            if (count($data)) {
                $query->values([$product->getId(), $websiteId, 1, $data['qty'], $data['stock_status']]);
            } else {
                $query->values([$product->getId(), $websiteId, 1, 1]);
            }
        }
        $query->execute();

        $this->_afterSave($product);
    }

    /**
     * @inheritDoc
     */
    protected function _beforeSave(Fci_Model_AbstractEntity $entity)
    {
        /** @var Fci_Model_Product_StockItem $stockItem */
        $stockItem = $entity->getData('stock_item');

        if ($stockItem->getData('qty') !== null) {
            if ($stockItem->getData('magage_stock') === null) {
                $stockItem->setData('magage_stock', 1);
                $stockItem->setData('use_config_manage_stock', 0);
            }
            if ($stockItem->getData('is_in_stock') === null) {
                $stockItem->setData('is_in_stock', $stockItem->getData('qty') > 0 ? 1 : 0);
            }
        }

        return parent::_beforeSave($entity);
    }
}
