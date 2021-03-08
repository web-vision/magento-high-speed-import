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
 */
class Fci_Model_Resource_Product_Link extends Fci_Model_Resource_AbstractResource
{
    /**
     * @var string
     */
    protected $_linkType;

    const RELATED = 'related';
    const CROSS_SELL = 'cross_sell';
    const UP_SELL = 'up_sell';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTable = 'catalog_product_link';
    }

    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $entity)
    {
        if (!$this->_linkType) {
            $this->_logger->error('No link type was set.');

            return $this;
        }

        $this->_beforeSave($entity);

        $linkTypeId = $this->_getLinkTypeId();
        $linkedProductData = $entity->getData($this->_linkType);

        if (!$linkedProductData) {
            return $this;
        }

        $insert = $this->_mysql->insert($this->getEntityTable())
            ->columns(['product_id', 'linked_product_id', 'link_type_id']);
        foreach ($linkedProductData as $linkedProductId => $position) {
            $insert->values([$entity->getId(), $linkedProductId, $linkTypeId]);
        }
        $insert->execute();

        $this->_afterSave($entity);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $entity)
    {
        if (!$this->_linkType) {
            $this->_logger->error('No link type was set.');

            return $this;
        }

        $this->_beforeSave($entity);

        $linkTypeId = $this->_getLinkTypeId();
        $linkedProductData = $entity->getData($this->_linkType);

        if (!$linkedProductData) {
            return $this;
        }

        // WVTODO don't delete all entries and re-add them, update position of existing one, delete extra one and insert new ones
        $this->_mysql->delete($this->getEntityTable())
            ->where('product_id', $entity->getId())
            ->where('link_type_id', $linkTypeId)
            ->execute();

        $insert = $this->_mysql->insert($this->getEntityTable())
            ->columns(['product_id', 'linked_product_id', 'link_type_id']);
        foreach ($linkedProductData as $linkedProductId => $position) {
            $insert->values([$entity->getId(), $linkedProductId, $linkTypeId]);
        }
        $insert->execute();

        $this->_afterSave($entity);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function _beforeSave(Fci_Model_AbstractEntity $entity)
    {
        $processedLinkedProducts = [];

        // before: sku => position; after: entity_id => position
        $linkedProducts = $entity->getData($this->_linkType);
        $skus = array_keys($linkedProducts);

        $linkedIds = $this->_mysql->select($entity->getResource()->getEntityTable(), ['entity_id', 'sku'])
            ->where('sku', $skus, 'in')
            ->fetchAll(null, PDO::FETCH_OBJ);

        if ($this->_mysql->count()) {
            foreach ($linkedIds as $linkedData) {
                $processedLinkedProducts[$linkedData->entity_id] = $linkedProducts[$linkedData->sku];
            }
        }

        $entity->setData($this->_linkType, $processedLinkedProducts);

        return parent::_beforeSave($entity);
    }

    /**
     * @inheritDoc
     */
    protected function _afterSave(Fci_Model_AbstractEntity $entity)
    {
        $linkTypeId = $this->_getLinkTypeId();

        $linkedProducts = $entity->getData($this->_linkType);
        $linkedProductIds = array_keys($linkedProducts);

        $linkIds = $this->_mysql->select($this->getEntityTable(), ['link_id', 'linked_product_id'])
            ->where('product_id', $entity->getId())
            ->where('link_type_id', $linkTypeId)
            ->where('linked_product_id', $linkedProductIds, 'in')
            ->fetchAll(null, PDO::FETCH_OBJ);

        $linkAttributeId = $this->_getLinkAttributeId();

        $insert = $this->_mysql->insert($this->getEntityTable('attribute_int'))
            ->columns(['product_link_attribute_id', 'link_id', 'value']);
        foreach ($linkIds as $linkedData) {
            $position = $linkedProducts[$linkedData->linked_product_id];
            $insert->values([$linkAttributeId, $linkedData->link_id, $position]);
        }
        $insert->execute();

        return parent::_afterSave($entity);
    }

    /**
     * Processes all link types.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return $this
     */
    public function processInsert(Fci_Model_AbstractEntity $entity)
    {
        if ($entity->hasData(static::RELATED)) {
            $this->_setLinkType(static::RELATED);
            $this->insert($entity);
        }

        if ($entity->hasData(static::CROSS_SELL)) {
            $this->_setLinkType(static::CROSS_SELL);
            $this->insert($entity);
        }

        if ($entity->hasData(static::UP_SELL)) {
            $this->_setLinkType(static::UP_SELL);
            $this->insert($entity);
        }

        $this->_setLinkType(null);

        return $this;
    }

    /**
     * Processes all link types.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return $this
     */
    public function processUpdate(Fci_Model_AbstractEntity $entity)
    {
        if ($entity->hasData(static::RELATED)) {
            $this->_setLinkType(static::RELATED);
            $this->update($entity);
        }

        if ($entity->hasData(static::CROSS_SELL)) {
            $this->_setLinkType(static::CROSS_SELL);
            $this->update($entity);
        }

        if ($entity->hasData(static::UP_SELL)) {
            $this->_setLinkType(static::UP_SELL);
            $this->update($entity);
        }

        $this->_setLinkType(null);

        return $this;
    }

    /**
     * Sets the link type.
     *
     * @param string $linkType
     *
     * @return $this
     */
    protected function _setLinkType($linkType)
    {
        $this->_linkType = $linkType;

        return $this;
    }

    /**
     * Returns the link type id based on the currently set link type.
     *
     * @return int
     */
    protected function _getLinkTypeId()
    {
        switch ($this->_linkType) {
            case static::RELATED:
                return (int)$this->_cache->getData('link_types/relation');
            case static::CROSS_SELL:
                return (int)$this->_cache->getData('link_types/cross_sell');
            case static::UP_SELL:
                return (int)$this->_cache->getData('link_types/up_sell');
        }

        return null;
    }

    /**
     * Returns the link attribute id for the position attribute based on the current link type.
     *
     * @return int
     */
    protected function _getLinkAttributeId()
    {
        $linkTypeId = $this->_getLinkTypeId();

        foreach ($this->_cache->getData('linkAttributes') as $linkAttributeId => $values) {
            if ($values['link_type_id'] === $linkTypeId && $values['code'] === 'position') {
                return $linkAttributeId;
            }
        }

        return null;
    }
}
