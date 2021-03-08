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
class Fci_Model_Resource_Category extends Fci_Model_Resource_AbstractResource
{
    /**
     * Fci_Model_Resource_Category constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTable = 'catalog_category_entity';
    }

    /**
     * @inheritDoc
     */
    public function insert(Fci_Model_AbstractEntity $category)
    {
        /** @var Fci_Model_Category $category */
        Cemes_Registry::get('insert_timer')->start();

        if (!$category->getData('name') || !$category->getData('path')) {
            $missing = ['name', 'path'];
            $mappings = $this->_config->getMappings();
            if ($mappings) {
                foreach ($missing as $id => $field) {
                    if (in_array($field, $mappings, true)) {
                        $missing[$id] = str_replace('_', ' ', array_search($field, $mappings, true));
                    }
                }
            }
            $context = [
                'pfad' => $category->getData('path'),
                'missing' => $missing,
            ];
            $this->_logger->error(
                'Kategorie mit dem Pfad "{pfad}" kann nicht importiert werden, da eines der folgenden Pflichtfelder leer ist. {missing}',
                $context
            );

            return $this;
        }

        $this->_beforeSave($category);

        $insert = $this->_mysql->insert($this->getEntityTable())
            ->set(
                'attribute_set_id',
                $category->getData('attribute_set_id')
            )
            ->set('parent_id', $category->getData('parent_id'))
            ->set('created_at', new Expression('NOW()'))
            ->set('updated_at', new Expression('NOW()'))
            ->set('path', $category->getData('path'))
            ->set('position', $category->getData('position'))
            ->set('level', $category->getData('level'))
            ->set('children_count', '0');

        if ($this->_fieldExistsInTable($this->getEntityTable(), 'entity_type_id')) {
            $insert->set('entity_type_id', $category->getData('entity_type_id'));
        }

        $insert->query();

        $category->setId($this->_mysql->lastInsertId());

        $category->getAttributeResource()->insert($category);

        $this->_afterSave($category);

        static::$_processedEntities++;
        static::$_insertedEntities++;

        $context = [
            'entitycount' => static::getProcessedEntities(),
            'name'        => $category->getData('name')->getValue(),
        ];
        $this->_logger->success(
            'Kategorie #{entitycount} mit dem Namen "{name}" erfolgreich eingetragen.',
            $context
        );

        Cemes_Registry::get('insert_timer')->stop();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update(Fci_Model_AbstractEntity $category)
    {
        /** @var Fci_Model_Category $category */
        Cemes_Registry::get('update_timer')->start();

        if (!$category->getData('path')) {
            $this->_logger->error('Kategorie kann nicht aktualisiert werden, da kein Pfad existiert.');

            return $this;
        }

        $this->_beforeSave($category);

        $update = $this->_mysql->update($this->getEntityTable())
            ->set('updated_at', new Expression('NOW()'))
            ->where('entity_id', $category->getId());

        if ($category->hasData('position')) {
            $update->set('position', $category->getData('position'));
        }
        $update->execute();

        $category->getAttributeResource()->update($category);

        $this->_afterSave($category);

        static::$_processedEntities++;
        static::$_updatedEntities++;

        $context = [
            'entitycount' => static::getProcessedEntities(),
            'name'        => $category->getData('name')->getValue(),
        ];
        $this->_logger->success(
            'Kategorie #{entitycount} mit dem Namen "{name}" erfolgreich aktuliaisert.',
            $context
        );

        Cemes_Registry::get('update_timer')->stop();

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _beforeSave(Fci_Model_AbstractEntity $category)
    {
        /** @var Fci_Model_Category $category */
        if (!$category->getId()) {
            if (!$category->hasData('position') || $category->getPosition() === '') {
                $category->setData('position', $this->_getMaxPosition($category->getPath()) + 1);
            }
            $path = explode('/', $category->getPath());
            $level = count($path);
            $category->setData('level', $level);
            if ($level) {
                $category->setData('parent_id', $path[$level - 1]);
            }
            $category->setData('path', $category->getPath() . '/');

            $parentIds = $path;

            $childrenCountField = $this->_mysql->quoteIdentifier('children_count');
            $this->_mysql->update($this->getEntityTable())
                ->set('children_count', new Expression($childrenCountField . ' + 1'))
                ->where('entity_id', $parentIds, 'in')
                ->execute();
        }

        return parent::_beforeSave($category);
    }

    /**
     * @inheritDoc
     */
    protected function _afterSave(Fci_Model_AbstractEntity $category)
    {
        /** @var Fci_Model_Category $category */
        if (substr($category->getPath(), -1) === '/') {
            $category->setData('path', $category->getPath() . $category->getId());
            $this->_mysql->update($this->getEntityTable())
                ->set('path', $category->getPath())
                ->where('entity_id', $category->getId())
                ->execute();
        }

        if ($category->isNew()) {
            // save category in cache
            // PHP has a Bug with affecting sub arrays in objects, so save array in var and overwrite the data in object with modified array
            $categories = $this->_cache->getData('categories');
            $identifier = $category->getData('name')->getValue() . ':' . $category->getData('parent_id');
            $categories[$identifier]['id'] = $category->getId();
            $categories[$identifier]['name'] = $category->getData('name')->getValue();
            $categories[$identifier]['level'] = $category->getData('level');
            $this->_cache->setData('categories', $categories);
            $this->_cache->save();
        }

        return parent::_afterSave($category);
    }

    /**
     * Get maximum position of child categories by specific tree path.
     *
     * @param string $path
     *
     * @return int
     */
    protected function _getMaxPosition($path)
    {
        $level = substr_count($path, '/') + 1;

        try {
            $position = $this->_mysql->select(
                    'catalog_category_entity',
                    ['position' => new Expression('MAX(position)')]
                )
                ->where('path', $path . '/%', 'like')
                ->where('level', $level)
                ->fetchEntry('position');
        } catch (Exception $e) {
            return 0;
        }

        if (!$position) {
            $position = 0;
        }

        return $position;
    }
}
