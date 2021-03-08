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
class Fci_Model_Resource_Product_Gallery extends Fci_Model_Resource_AbstractResource
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTable = 'catalog_product_entity_media_gallery';
    }

    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $product)
    {
        /** @var Fci_Model_Product $product */
        if (!$product->hasData('gallery')) {
            return;
        }

        /** @var Fci_Helper_Gallery $gallery */
        $gallery = $product->getData('gallery');

        if (!$gallery->hasImages()) {
            return;
        }

        // insert file into media gallery
        $insert = $this->_mysql->insert($this->getEntityTable());

        if ($this->_fieldExistsInTable($this->getEntityTable(), 'entity_id')) {
            $insert->columns(['attribute_id', 'entity_id', 'value']);
        } else {
            $insert->columns(['attribute_id', 'value']);
        }

        foreach ($gallery->getImages() as $image) {
            if ($this->_fieldExistsInTable($this->getEntityTable(), 'entity_id')) {
                $insert->values(
                    [
                        $this->_cache->getProductAttribute('media_gallery')->getAttributeId(),
                        $product->getId(),
                        $image,
                    ]
                );
            } else {
                $insert->values(
                    [
                        $this->_cache->getProductAttribute('media_gallery')->getAttributeId(),
                        $image,
                    ]
                );
            }
        }
        $insert->execute();

        // get value ids for inserted file names
        $select = $this->_mysql->select($this->getEntityTable(), ['value_id', 'value']);
        foreach ($gallery->getImages() as $image) {
            $select->orWhere('value', $image, 'like');
        }
        $tmp = $select->fetchAll(null, PDO::FETCH_OBJ);
        $values = [];
        foreach ($tmp as $img) {
            $values[$img->value] = $img->value_id;
        }
        // insert label, position, disabled and excluded into media gallery value
        $insert = $this->_mysql->insert($this->getEntityTable('value'));

        if ($this->_fieldExistsInTable($this->getEntityTable('value'), 'entity_id')) {
            $insert->columns(['value_id', 'store_id', 'entity_id', 'label', 'position', 'disabled']);
        } else {
            $insert->columns(['value_id', 'store_id', 'label', 'position', 'disabled']);
        }

        foreach ($gallery->getImages() as $pos => $image) {
            if ($this->_fieldExistsInTable($this->getEntityTable('value'), 'entity_id')) {
                $insert->values(
                    [
                        $values[$image],
                        0,
                        $product->getId(),
                        $gallery->getLabel($image),
                        $pos + 1,
                        $gallery->isExcluded($image),
                    ]
                );
            } else {
                $insert->values(
                    [
                        $values[$image],
                        0,
                        $gallery->getLabel($image),
                        $pos + 1,
                        $gallery->isExcluded($image),
                    ]
                );
            }
        }
        $insert->execute();

        // magento 2 only table for relation between image and product
        if ($this->_tableExists($this->getEntityTable('value_to_entity'))) {
            $insert = $this->_mysql->insert($this->getEntityTable('value_to_entity'))
                ->columns(['value_id', 'entity_id']);
            foreach ($values as $valueId) {
                $insert->values([$valueId, $product->getId()]);
            }
            $insert->execute();
        }

        // settings for images
        $insert = $this->_mysql->insert($product->getResource()->getEntityTable('varchar'));

        if ($this->_fieldExistsInTable($product->getResource()->getEntityTable('varchar'), 'entity_type_id')) {
            $insert->columns(['entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value']);
            $insert->values(
                [
                    $product->getEntityTypeId(),
                    $this->_cache->getData('productAttributes/image/attribute_id'),
                    '0',
                    $product->getId(),
                    $gallery->getImage(),
                ]
            );
            $insert->values(
                [
                    $product->getEntityTypeId(),
                    $this->_cache->getData('productAttributes/small_image/attribute_id'),
                    '0',
                    $product->getId(),
                    $gallery->getSmallImage(),
                ]
            );
            $insert->values(
                [
                    $product->getEntityTypeId(),
                    $this->_cache->getData('productAttributes/thumbnail/attribute_id'),
                    '0',
                    $product->getId(),
                    $gallery->getThumbnail(),
                ]
            );
        } else {
            $insert->columns(['attribute_id', 'store_id', 'entity_id', 'value']);
            $insert->values(
                [
                    $this->_cache->getData('productAttributes/image/attribute_id'),
                    '0',
                    $product->getId(),
                    $gallery->getImage(),
                ]
            );
            $insert->values(
                [
                    $this->_cache->getData('productAttributes/small_image/attribute_id'),
                    '0',
                    $product->getId(),
                    $gallery->getSmallImage(),
                ]
            );
            $insert->values(
                [
                    $this->_cache->getData('productAttributes/thumbnail/attribute_id'),
                    '0',
                    $product->getId(),
                    $gallery->getThumbnail(),
                ]
            );
        }
        $insert->execute();
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $product)
    {
        /** @var Fci_Model_Product $product */
        if (!$product->hasData('gallery')) {
            return;
        }

        /** @var Fci_Helper_Gallery $gallery */
        $gallery = $product->getData('gallery');

        if (!$gallery->hasImages()) {
            return;
        }

        // delete old entries
        if ($this->_fieldExistsInTable($this->getEntityTable(), 'entity_id')) {
            $this->_mysql->delete($this->getEntityTable())
                ->where('entity_id', $product->getId())
                ->execute();
        } else {
            $valueIds = $this->_mysql->select($this->getEntityTable('value_to_entity'), ['value_id'])
                ->where('entity_id', $product->getId())
                ->fetchAll('value_id');

            if ($valueIds) {
                $this->_mysql->delete($this->getEntityTable())
                    ->where('value_id', $valueIds, 'in')
                    ->execute();
            }
        }

        $this->_mysql->delete($product->getResource()->getEntityTable('varchar'))
            ->where('entity_id', $product->getId())
            ->where('attribute_id', $this->_cache->getData('productAttributes/image/attribute_id'), 'eq', '1')
            ->orWhere('attribute_id', $this->_cache->getData('productAttributes/small_image/attribute_id'), 'eq', '1')
            ->orWhere('attribute_id', $this->_cache->getData('productAttributes/thumbnail/attribute_id'), 'eq', '1')
            ->execute();

        // insert new data
        $this->insert($product);
    }
}
