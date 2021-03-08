<?php
/* WVTODO:
 * read(), write() mit parameter um nur bestimmten cache neu zu schreiben
 */

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
 * @package     Fci_Objects
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Objects_Cache extends Cemes_Object
{
    /**
     * @var \Cemes\Pdo\Mysql
     */
    protected $mysql;

    /**
     * @var Fci_Helper_Factory_MagentoFactory
     */
    protected $_magento;

    /**
     * @var Fci_Objects_Cache
     */
    protected static $instance;

    /**
     * @var array
     */
    protected static $YESNO = [
        0 => [
            'No',
            'Nein',
        ],
        1 => [
            'Yes',
            'Ja',
        ],
    ];

    /**
     * Fci_Objects_Cache constructor.
     *
     * @param bool  $force
     * @param array $data
     */
    public function __construct($force, array $data = [])
    {
        parent::__construct($data);

        $this->_magento = Fci_Helper_Factory_MagentoFactory::getMagento();

        if ($force || !is_file(ROOT_BASEDIR . 'bin/cache.php')) {
            try {
                $this->write();
            } catch (Exception $e) {
                Cemes_Registry::get('logger')->critical($e->getMessage());
            }
        } else {
            $this->read();
        }
    }

    /**
     * Singleton method to return just one instance of the class.
     *
     * @param bool $force
     *
     * @return Fci_Objects_Cache
     */
    public static function getInstance($force = false)
    {
        if (self::$instance === null) {
            self::$instance = new self($force);
        }

        return self::$instance;
    }

    /**
     * Reads all data from the database and saves it into the cache file.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function write()
    {
        $this->mysql = Cemes_Registry::get('db');
        $this->_getWebsiteStore();
        $this->_getEntityTypeIds();

        $this->_magento->init();
        $this->_getProductAttributes();
        $this->_getCategoryAttributes();
        $this->_magento->unload();
        Cemes_Autoloader::resetAutoloaders();

        $this->_getLinkAttributes();
        $this->_getCategories();
        $this->_getAttributeSets();
        $this->_getCustomerGroups();
        $this->_getLinkTypes();
        unset($this->mysql);

        $this->save();
    }

    /**
     * Reads the data from the cache file.
     */
    public function read()
    {
        $cache = file_get_contents(ROOT_BASEDIR . 'bin/cache.php');
        $this->_data = eval($cache);
    }

    /**
     * Saves the current data into the cache file.
     */
    public function save()
    {
        $handle = fopen(ROOT_BASEDIR . 'bin/cache.php', 'wb');
        fwrite($handle, 'return ' . var_export($this->_data, true) . ';');
        fclose($handle);
    }

    /********************************************************************/
    /************************* get data from db *************************/
    /********************************************************************/

    /**
     * Reads the entity type ids from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getEntityTypeIds()
    {
        $rows = $this->mysql->select('eav_entity_type', ['entity_type_id', 'entity_type_code'])
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rows === false) {
            return;
        }
        $entityTypeIds = [];
        foreach ($rows as $row) {
            $entityTypeIds[$row->entity_type_code] = (int)$row->entity_type_id;
        }
        $this->_data['entityTypeIds'] = $entityTypeIds;
    }

    /**
     * Reads the product attributes from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getProductAttributes()
    {
        $ignore = [
            'required_options',
            'has_options',
            'created_at',
            'updated_at',
            'minimal_price',
            'price_view',
            'gallery',
            'is_recurring',
            'recurring_profile',
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update',
            'page_layout',
        ];

        $this->_getAttributes($this->getData('entityTypeIds/catalog_product'), 'productAttributes', $ignore);
    }

    /**
     * Reads the category attributes from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getCategoryAttributes()
    {
        $this->_getAttributes($this->getData('entityTypeIds/catalog_category'), 'categoryAttributes');
    }

    /**
     * Gets all attributes for the given type id and saves them in the given area while ignoring the attributes given in
     * ignore.
     *
     * @param int    $typeId
     * @param string $area
     * @param array  $ignore
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getAttributes($typeId, $area, array $ignore = [])
    {
        $rows = $this->mysql->select(
            'eav_attribute',
            [
                'attribute_code',
                'attribute_id',
                'backend_type',
                'frontend_input',
                'frontend_label',
                'is_required',
                'default_value',
                'is_user_defined',
                'source_model',
            ]
            )
            ->where('entity_type_id', $typeId)
            ->where('frontend_input', '', 'neq')
            ->fetchAll(null, PDO::FETCH_OBJ);

        if ($rows === false) {
            $this->_data[$area] = false;

            return;
        }

        $this->_data[$area] = [];
        $attributeIdToCodeMap = [];
        foreach ($rows as $row) {
            if (in_array($row->attribute_code, $ignore, true)) {
                continue;
            }
            $attributeIdToCodeMap[$row->attribute_id] = $row->attribute_code;
            $this->_data[$area][$row->attribute_code]['attribute_id'] = $row->attribute_id;
            $this->_data[$area][$row->attribute_code]['attribute_code'] = $row->attribute_code;
            $this->_data[$area][$row->attribute_code]['backend_type'] = $row->backend_type;
            $this->_data[$area][$row->attribute_code]['frontend_input'] = $row->frontend_input;
            $this->_data[$area][$row->attribute_code]['label'] = $row->frontend_label;
            $this->_data[$area][$row->attribute_code]['is_required'] = $row->is_required;
            $this->_data[$area][$row->attribute_code]['default_value'] = $row->default_value === ''
            || $row->default_value === null ? false : $row->default_value;
            $this->_data[$area][$row->attribute_code]['is_user_defined'] = (int)$row->is_user_defined;
            $this->_data[$area][$row->attribute_code]['source_model'] = $row->source_model;
            if ($row->frontend_input === 'select' || $row->frontend_input === 'multiselect') {
                $this->_getAttributeValues($row, $area);
                switch ($row->attribute_code) {
                    case 'options_container':
                        // WVTODO don't replace completely just add german values
                        $attrs[$row->attribute_code]['values'] = [
                            'container1' => [
                                'Product Info Column',
                                'Artikelinformationsspalte',
                            ],
                            'container2' => [
                                'Block after Info Column',
                                'Block nach der Info-Spalte',
                            ],
                        ];
                        break;
                }
            } elseif ($row->frontend_input === 'boolean') {
                if ($row->source_model === '') {
                    $this->_data[$area][$row->attribute_code]['values'] = self::$YESNO;
                } else {
                    $this->_getAttributeValues($row, $area);
                }
            }
        }

        // scope
        $scopes = $this->mysql->select('catalog_eav_attribute', ['attribute_id', 'is_global'])
            ->where('attribute_id', array_keys($attributeIdToCodeMap), 'in')
            ->fetchAll(null, PDO::FETCH_OBJ);
        foreach ($scopes as $scope) {
            $attributeCode = $attributeIdToCodeMap[$scope->attribute_id];
            $this->_data[$area][$attributeCode]['scope'] = (int)$scope->is_global;
        }
        $attributeIdToCodeMap = null;
        unset($attributeIdToCodeMap);

        // translations
        foreach ($this->_data['store'] as $storeId => $storeData) {
            if ($storeId === 0) {
                continue;
            }

            $this->_magento->setLocale($storeId);
            $this->_magento->setCurrentStore($storeData['code']);

            foreach ($this->_data[$area] as $attributeCode => $attributeData) {
                $localizedValues = [];

                if ($attributeData['source_model'] !== ''
                    && $attributeData['source_model'] !== 'eav/entity_attribute_source_table'
                    && $attributeData['source_model'] !== 'Magento\Eav\Model\Entity\Attribute\Source\Table'
                    && $attributeData['source_model'] !== null) {
                    $magValues = $this->_magento->getAttributeOptions($attributeData['source_model']);

                    foreach ($magValues as $magValue) {
                        if (is_array($magValue['value'])) {
                            $magValue = reset($magValue['value']);
                        }
                        if ($magValue['value'] !== '') {
                            $localizedValues[$magValue['value']] = '' . $magValue['label'];
                        }
                    }

                    foreach ($this->_data[$area][$attributeCode]['values'] as $id => &$labels) {
                        if (!array_key_exists($id, $localizedValues)) {
                            continue;
                        }

                        if (!is_array($labels)) {
                            $labels = [$labels];
                        }

                        $labels[$storeId] = $localizedValues[$id];
                    }
                    unset($labels);
                }
            }
        }
    }

    /**
     * Reads option values from the database or the source model class.
     *
     * @param \stdClass $eav_attribute
     * @param string    $area
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getAttributeValues(stdClass $eav_attribute, $area)
    {
        if ($eav_attribute->source_model === ''
            || $eav_attribute->source_model === 'eav/entity_attribute_source_table'
            || $eav_attribute->source_model === 'Magento\Eav\Model\Entity\Attribute\Source\Table'
            || $eav_attribute->source_model === null) {
            $rows = $this->mysql->select('eav_attribute_option', ['option_id'])
                ->leftJoin(
                    'eav_attribute_option_value',
                    'eav_attribute_option.option_id = eav_attribute_option_value.option_id',
                    ['value', 'store_id']
                )
                ->where('eav_attribute_option.attribute_id', $eav_attribute->attribute_id)
                ->orderBy('store_id')
                ->fetchAll(null, PDO::FETCH_OBJ);
            if ($rows === false) {
                return;
            }
            $values = [];
            foreach ($rows as $row) {
                if (array_key_exists($row->option_id, $values)) {
                    $values[$row->option_id][$row->store_id] = $row->value;
                } else {
                    $values[$row->option_id] = [$row->store_id => $row->value];
                }
            }
        } else {
            $magValues = $this->_magento->getAttributeOptions($eav_attribute->source_model);

            $values = [];
            foreach ($magValues as $magValue) {
                if (is_array($magValue['value'])) {
                    $magValue = reset($magValue['value']);
                }
                if ($magValue['value'] !== '') {
                    $values[$magValue['value']] = [0 => '' . $magValue['label']];
                }
            }
        }

        $this->_data[$area][$eav_attribute->attribute_code]['values'] = $values;
    }

    /**
     * Reads the link attributes from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getLinkAttributes()
    {
        $rows = $this->mysql->select('catalog_product_link_attribute')
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rows === false) {
            $this->_data['linkAttributes'] = false;

            return;
        }
        $linkAttributes = [];
        foreach ($rows as $row) {
            $productLinkAttributeId = (int)$row->product_link_attribute_id;
            $linkAttributes[$productLinkAttributeId]['link_type_id'] = (int)$row->link_type_id;
            $linkAttributes[$productLinkAttributeId]['code'] = $row->product_link_attribute_code;
            $linkAttributes[$productLinkAttributeId]['data_type'] = $row->data_type;
        }
        $this->_data['linkAttributes'] = $linkAttributes;
    }

    /**
     * Reads all categories from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getCategories()
    {
        $attributeId = $this->getData('categoryAttributes/name/attribute_id');
        // get all category names with id, parent id and store
        $rows = $this->mysql->select('catalog_category_entity', ['entity_id', 'parent_id', 'level'])
            ->leftJoin(
                'catalog_category_entity_varchar',
                'catalog_category_entity.entity_id = catalog_category_entity_varchar.entity_id',
                ['name' => 'value']
            )
            ->where('catalog_category_entity_varchar.attribute_id', $attributeId)
            ->where('catalog_category_entity_varchar.store_id', 0)
            ->orderBy('catalog_category_entity.path')
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rows === false) {
            $this->_data['categories'] = false;

            return;
        }
        $categories = [];
        foreach ($rows as $row) {
            // name + parent id as identifier for easier search in array
            $identifier = $row->name . ':' . $row->parent_id;
            $categories[$identifier]['id'] = (int)$row->entity_id;
            $categories[$identifier]['name'] = $row->name;
            $categories[$identifier]['level'] = (int)$row->level;
        }
        $this->_data['categories'] = $categories;
    }

    /**
     * Reads all product attribute sets and the category attribute set from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getAttributeSets()
    {
        $entityTypeIds = [
            $this->getData('entityTypeIds/catalog_category'),
            $this->getData('entityTypeIds/catalog_product'),
        ];
        $rows = $this->mysql->select('eav_attribute_set', ['attribute_set_id', 'attribute_set_name', 'entity_type_id'])
            ->where('entity_type_id', $entityTypeIds, 'in')
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rows === false) {
            $this->_data['categoryAttributeSet'] = false;
            $this->_data['productAttributeSets'] = false;

            return;
        }
        $categoryAttributeSets = false;
        $productAttributeSets = [];
        foreach ($rows as $row) {
            if ($entityTypeIds[0] === (int)$row->entity_type_id) {
                $categoryAttributeSets = (int)$row->attribute_set_id;
            } else {
                $productAttributeSets[(int)$row->attribute_set_id] = $row->attribute_set_name;
            }
        }
        $this->_data['categoryAttributeSet'] = $categoryAttributeSets;
        $this->_data['productAttributeSets'] = $productAttributeSets;
    }

    /**
     * Reads the customer groups from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getCustomerGroups()
    {
        $rows = $this->mysql->select('customer_group', ['customer_group_id', 'customer_group_code'])
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rows === false) {
            $this->_data['customer_groups'] = false;

            return;
        }
        $customerGroups = [];
        foreach ($rows as $row) {
            $customerGroups[(int)$row->customer_group_id] = $row->customer_group_code;
        }
        $this->_data['customer_groups'] = $customerGroups;
    }

    /**
     * Reads all link types from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getLinkTypes()
    {
        $rows = $this->mysql->select('catalog_product_link_type')
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rows === false) {
            $this->_data['link_types'] = false;

            return;
        }
        $linkTypes = [];
        foreach ($rows as $row) {
            $linkTypes[$row->code] = (int)$row->link_type_id;
        }
        $this->_data['link_types'] = $linkTypes;
    }

    /**
     * Reads all websites and stores from the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _getWebsiteStore()
    {
        $this->_data['websites'] = [];
        $this->_data['store'] = [];
        $websites = $this->mysql->select($this->_magento->getTable('websites'), ['website_id', 'code', 'name'])
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($websites === false) {
            return;
        }

        foreach ($websites as $website) {
            $websiteId = (int)$website->website_id;
            $this->_data['websites'][$websiteId]['code'] = $website->code;
            $this->_data['websites'][$websiteId]['name'] = $website->name;
            $this->_data['websites'][$websiteId]['stores'] = [];
        }

        $stores = $this->mysql->select($this->_magento->getTable('stores'), ['website_id', 'store_id', 'code', 'name'])
            ->fetchAll(null, PDO::FETCH_OBJ);

        if ($stores !== false) {
            foreach ($stores as $store) {
                $storeId = (int)$store->store_id;
                $websiteId = (int)$store->website_id;
                $this->_data['store'][$storeId]['code'] = $store->code;
                $this->_data['store'][$storeId]['name'] = $store->name;
                $this->_data['websites'][$websiteId]['stores'][] = $storeId;
            }
        }

        $rootCategories = $this->mysql->select($this->_magento->getTable('store_groups'), ['website_id', 'root_category_id'])
            ->fetchAll(null, PDO::FETCH_OBJ);
        if ($rootCategories !== false) {
            foreach ($rootCategories as $rootCategory) {
                $websiteId = (int)$rootCategory->website_id;
                $this->_data['websites'][$websiteId]['root_category'] = (int)$rootCategory->root_category_id;
            }
        }
    }

    /**
     * Returns an id if the given value exists in the given area of data.
     *
     * @param string $name
     * @param string $area
     *
     * @return int|string
     */
    public function getIdByName($name, $area)
    {
        foreach ($this->_data[$area] as $id => $value) {
            if ($value == $name) {
                return $id;
            }
        }

        return null;
    }

    /********************************************************************/
    /************************* helper functions *************************/
    /********************************************************************/
    /**
     * Returns the product attribute for the given attribute code.
     *
     * @param string $attributeCode
     *
     * @return Fci_Model_Attribute
     */
    public function getProductAttribute($attributeCode)
    {
        $attribute = new Fci_Model_Attribute($this->getData('productAttributes/' . $attributeCode));
        if (!$attribute->isEmpty()) {
            $attribute->setAttributeCode($attributeCode);
        }

        return $attribute;
    }

    /**
     * Returns the category attribute for the given attribute code.
     *
     * @param string $attributeCode
     *
     * @return Fci_Model_Attribute
     */
    public function getCategoryAttribute($attributeCode)
    {
        $attribute = new Fci_Model_Attribute($this->getData('categoryAttributes/' . $attributeCode));
        if (!$attribute->isEmpty()) {
            $attribute->setAttributeCode($attributeCode);
        }

        return $attribute;
    }

    /**
     * Checks if the given id or code is a valid website id or code.
     *
     * @param string|int $idOrCode
     *
     * @return bool
     */
    public function validateWebsite($idOrCode)
    {
        if (is_numeric($idOrCode)) {
            if (array_key_exists($idOrCode, $this->_data['websites'])) {
                return (int)$idOrCode;
            }
        } else {
            foreach ($this->_data['websites'] as $id => $data) {
                if ($data['code'] === $idOrCode) {
                    return $id;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the given id or name is a valid attribute set id or name.
     *
     * @param string|int $idOrName
     *
     * @return bool
     */
    public function validateAttributeSet($idOrName)
    {
        if (is_numeric($idOrName)) {
            if (array_key_exists($idOrName, $this->_data['productAttributeSets'])) {
                return (int)$idOrName;
            }
        } else {
            foreach ($this->_data['productAttributeSets'] as $id => $name) {
                if ($name === $idOrName) {
                    return $id;
                }
            }
        }

        return null;
    }

    /**
     * Checks if the given id or name is a valid customer group id or name.
     *
     * @param string|int $idOrName
     *
     * @return bool
     */
    public function validateCustomerGroup($idOrName)
    {
        if (is_numeric($idOrName)) {
            if (array_key_exists($idOrName, $this->_data['customer_groups'])) {
                return (int)$idOrName;
            }
        } else {
            foreach ($this->_data['customer_groups'] as $id => $name) {
                if ($name === $idOrName) {
                    return $id;
                }
            }
        }

        return false;
    }
}
