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
class Fci_Model_Resource_Attribute extends Fci_Model_Resource_AbstractResource
{
    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $entity)
    {
        $allAttributes = [
            'datetime' => [],
            'decimal'  => [],
            'int'      => [],
            'text'     => [],
            'varchar'  => [],
        ];
        foreach ($entity->getData() as $attributeCode => $attribute) {
            if (!$attribute instanceof Fci_Model_Attribute) {
                continue;
            }
            /** @var Fci_Model_Attribute $attribute */
            $backendType = $attribute->getBackendType();
            if (!array_key_exists($backendType, $allAttributes)) {
                continue;
            }

            $allAttributes[$backendType][] = $attribute;
        }

        /** @var array $attributes */
        foreach ($allAttributes as $backendType => $attributes) {
            if (!$attributes) {
                continue;
            }
            $table = $entity->getResource()->getEntityTable($backendType);
            $insert = $this->_mysql->insert($table);

            if ($this->_fieldExistsInTable($table, 'entity_type_id')) {
                $insert->columns(['entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value']);
            } else {
                $insert->columns(['attribute_id', 'store_id', 'entity_id', 'value']);
            }

            foreach ($attributes as $attribute) {
                if ($attribute->getValue() === 'NULL') {
                    continue;
                }
                if ($this->_fieldExistsInTable($table, 'entity_type_id')) {
                    $insert->values(
                        [
                            $entity->getEntityTypeId(),
                            $attribute->getAttributeId(),
                            0,
                            $entity->getId(),
                            $attribute->getValue(),
                        ]
                    );
                } else {
                    $insert->values(
                        [
                            $attribute->getAttributeId(),
                            0,
                            $entity->getId(),
                            $attribute->getValue(),
                        ]
                    );
                }
            }
            $insert->execute();
        }
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $entity)
    {
        $updateAttributes = $deleteAttributes = [
            'datetime' => [],
            'decimal'  => [],
            'int'      => [],
            'text'     => [],
            'varchar'  => [],
        ];
        foreach ($entity->getData() as $attributeCode => $attribute) {
            if (!$attribute instanceof Fci_Model_Attribute) {
                continue;
            }
            /** @var Fci_Model_Attribute $attribute */
            $backendType = $attribute->getBackendType();
            if (!array_key_exists($backendType, $updateAttributes)) {
                continue;
            }

            if ($attribute->getValue() === 'NULL') {
                $deleteAttributes[$backendType][] = $attribute;
            } else {
                $updateAttributes[$backendType][] = $attribute;
            }
        }

        /** @var array $attributes */
        foreach ($updateAttributes as $backendType => $attributes) {
            if (!$attributes) {
                continue;
            }
            $table = $entity->getResource()->getEntityTable($backendType);
            $replace = $this->_mysql->replace($table);

            if ($this->_fieldExistsInTable($table, 'entity_type_id')) {
                $replace->columns(['entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value']);
            } else {
                $replace->columns(['attribute_id', 'store_id', 'entity_id', 'value']);
            }

            foreach ($attributes as $attribute) {
                // get store ids based on scope
                switch ($attribute->getScope()) {
                    case 2: // website
                        $storeIds = [0];
                        foreach ($entity->getData('websites') as $websiteId) {
                            foreach ($this->_cache->getData('websites/' . $websiteId . '/stores') as $storeId) {
                                $storeIds[] = $storeId;
                            }
                        }
                        $storeIds = array_unique($storeIds);
                        break;
                    case 1: // global
                        $storeIds = [0];
                        break;
                    default: // store_view
                        $storeIds = array_merge([0], $entity->getData('stores'));
                }

                // remove the store view id 0 if scope is not global and importGlobally = 0
                if ($attribute->getScope() !== 1 && !$this->_config->getImportGlobally()) {
                    array_splice($storeIds, 0, 1);
                }

                foreach ($storeIds as $storeId) {
                    if ($this->_fieldExistsInTable($table, 'entity_type_id')) {
                        $replace->values(
                            [
                                $entity->getEntityTypeId(),
                                $attribute->getAttributeId(),
                                $storeId,
                                $entity->getId(),
                                $attribute->getValue(),
                            ]
                        );
                    } else {
                        $replace->values(
                            [
                                $attribute->getAttributeId(),
                                $storeId,
                                $entity->getId(),
                                $attribute->getValue(),
                            ]
                        );
                    }

                    // if importGlobally = 1 stop loop after first iteration (store id 0)
                    if ($this->_config->getImportGlobally()) {
                        break;
                    }
                }
            }
            $replace->execute();
        }

        /** @var array $attributes */
        foreach ($deleteAttributes as $backendType => $attributes) {
            if (!$attributes) {
                continue;
            }
            $attributeIds = [];
            foreach ($attributes as $attribute) {
                $attributeIds[] = $attribute->getAttributeId();
            }

            $this->_mysql->delete($entity->getResource()->getEntityTable($backendType))
                ->where('entity_id', $entity->getId())
                ->where('attribute_id', $attributeIds, 'in')
                ->execute();
        }
    }
}
