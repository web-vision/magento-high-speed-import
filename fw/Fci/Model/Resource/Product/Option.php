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
class Fci_Model_Resource_Product_Option extends Fci_Model_Resource_AbstractResource
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTable = 'catalog_product_option';
    }

    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $product)
    {
        $productId = $product->getId();

        if (count($product->getData('options')) !== 0) {
            /** @var Fci_Objects_Option $option */
            foreach ($product->getData('options') as $option) {
                /******************** Simple Product ********************/
                $this->_mysql->update($product->getResource()->getEntityTable())
                    ->set('has_options', 1)
                    ->where('entity_id', $productId)
                    ->execute();

                /******************** Simple Product Option ********************/
                $insert = $this->_mysql->insert($this->getEntityTable())
                    ->set('product_id', $productId)
                    ->set('type', ''.$option->getType())
                    ->set('is_require', $option->isRequired())
                    ->set('sort_order', $option->getPosition());
                if ($option->getSku() !== null) {
                    $insert->set('sku', $option->getSku());
                }
                if ($option->getAdditional() !== null) {
                    $additional = $option->getAdditional();
                    switch ($option->getType()) {
                        case Fci_Enum_OptionType::FELD():
                        case Fci_Enum_OptionType::BEREICH():
                            $insert->set('max_characters', $additional[0]);
                            break;
                        case Fci_Enum_OptionType::DATEI():
                            if ($additional[0] !== '') {
                                $insert->set('file_extension', $additional[0]);
                            }
                            if ($additional[1] !== '') {
                                $insert->set('image_size_x', $additional[1]);
                            }
                            if ($additional[2] !== '') {
                                $insert->set('image_size_y', $additional[2]);
                            }
                            break;
                    }
                }
                $insert->query();

                $optionId = $this->_mysql->lastInsertId();

                /******************** Option Price ********************/
                $insert = $this->_mysql->insert($this->getEntityTable('price'))
                    ->set('option_id', $optionId)
                    ->set('store_id', 0);
                if ($option->getPrice() !== null) {
                    $insert->set('price', $option->getPrice());
                    if ($option->getPriceType() !== null) {
                        $insert->set('price_type', '' . $option->getPriceType());
                    }
                }
                $insert->execute();

                /******************** Option Title ********************/
                $this->_mysql->insert($this->getEntityTable('title'))
                    ->set('option_id', $optionId)
                    ->set('store_id', 0)
                    ->set('title', $option->getTitle())
                    ->execute();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $product)
    {
        $productId = $product->getId();

        if (count($product->getData('options')) !== 0) {
            /** @var Fci_Objects_Option $option */
            foreach ($product->getData('options') as $option) {
                $this->_mysql->select($this->getEntityTable(), ['option_id'])
                    ->leftJoin(
                        $this->getEntityTable('title'),
                        'catalog_product_option.option_id = catalog_product_option_title.option_id'
                    )
                    ->where('title', $option->getTitle())
                    ->where('store_id', 0)
                    ->where('product_id', $productId)
                    ->query();
                if ($this->_mysql->count() === 0) {
                    $this->insert($option, $productId);
                } else {
                    $optionId = $this->_mysql->fetchEntry(null, 'option_id');
                    /******************** Simple Product Option ********************/
                    $old = $this->_mysql->select($this->getEntityTable())
                        ->where('option_id', $optionId)
                        ->fetchEntry();
                    $hasChanges = false;
                    $update = $this->_mysql->update($this->getEntityTable());
                    if ($old->type != $option->getType()) {
                        $update->set('type', $option->getType());
                        $hasChanges = true;
                    }
                    if ($old->isRequired != $option->isRequired()) {
                        $update->set('isRequire', $option->isRequired());
                        $hasChanges = true;
                    }
                    if ($old->sku !== $option->getSku()) {
                        $update->set('sku', $option->getSku());
                        $hasChanges = true;
                    }
                    if ($option->getAdditional() !== null) {
                        $additional = $option->getAdditional();
                        switch ($option->getType()) {
                            case Fci_Enum_OptionType::FELD():
                            case Fci_Enum_OptionType::BEREICH():
                                if ($old->max_characters != $additional[0]) {
                                    $update->set('max_characters', $additional[0]);
                                    $hasChanges = true;
                                }
                                break;
                            case Fci_Enum_OptionType::DATEI():
                                if ($old->file_extension != $additional[0]) {
                                    $update->set('file_extension', $additional[0]);
                                    $hasChanges = true;
                                }
                                if ($old->image_size_x != $additional[1]) {
                                    $update->set('image_size_x', $additional[1]);
                                    $hasChanges = true;
                                }
                                if ($old->image_size_y != $additional[2]) {
                                    $update->set('image_size_y', $additional[2]);
                                    $hasChanges = true;
                                }
                                break;
                        }
                    }
                    if ($hasChanges) {
                        $update->execute();
                    }

                    /******************** Option Price ********************/
                    $old = $this->_mysql->select($this->getEntityTable('price'))
                        ->where('option_id', $optionId)
                        ->where('store_id', 0)
                        ->fetchEntry();
                    $hasChanges = false;
                    $update = $this->_mysql->update($this->getEntityTable('price'));
                    if ($option->getPrice() !== null && $old->price != $option->getPrice()) {
                        $update->set('price', $option->getPrice());
                        $hasChanges = true;
                    }
                    if ($option->getPriceType() !== null && $old->price_type != '' . $option->getPriceType()) {
                        $update->set('price_type', '' . $option->getPriceType());
                        $hasChanges = true;
                    }
                    if ($hasChanges) {
                        $update->execute();
                    }
                }
            }
        }
    }
}
