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
class Fci_Helper_Attribute
{
    /**
     * @var Fci_Helper_Attribute
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $_attributeStoreViews = [0];

    /**
     * @var array
     */
    protected $_defaults = [];

    /**
     * @var bool
     */
    protected $_importGlobally = true;

    /**
     * @var string
     */
    protected $_dateTimeFormat = 'Y.m.d H:i:s';

    /**
     * @var Fci_Objects_Cache
     */
    protected $_cache;

    /**
     * @var Fci_Log_LoggerInterface
     */
    protected $_logger;

    /**
     * @var Fci_Model_Attribute
     */
    protected $_attribute;

    /**
     * Fci_Helper_Attribute constructor.
     */
    private function __construct()
    {
        $this->_cache = Fci_Objects_Cache::getInstance();
        $this->_logger = Cemes_Registry::get('logger');
    }

    /**
     * Private clone method to prevent cloning the object.
     */
    private function __clone()
    {
    }

    /**
     * Singleton method to return just one instance of the class.
     *
     * @return Fci_Helper_Attribute
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Setter for the store views.
     *
     * @param array $attributeStoreViews
     */
    public function setAttributeStoreViews($attributeStoreViews)
    {
        $this->_attributeStoreViews = $attributeStoreViews;
    }

    /**
     * Setter for the default attribute values.
     *
     * @param array $defaults
     */
    public function setDefaults($defaults)
    {
        $this->_defaults = $defaults;
    }

    /**
     * Setter if the attribute should be imported globally.
     *
     * @param bool $importGlobally
     */
    public function setImportGlobally($importGlobally)
    {
        $this->_importGlobally = $importGlobally;
    }

    /**
     * Getter if the attribute should be imported globally.
     *
     * @return bool
     */
    public function getImportGlobally()
    {
        return $this->_importGlobally;
    }

    /**
     * Setter for the date time format which will be used if PHP can't recognize the format.
     *
     * @param string $dateTimeFormat
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->_dateTimeFormat = $dateTimeFormat;
    }

    /**
     * Sets the attribute which will be processed.
     *
     * @param Fci_Model_Attribute $attribute
     */
    public function setAttribute(Fci_Model_Attribute $attribute)
    {
        $this->_attribute = $attribute;
    }

    /**
     * Normalizes the value for the database and returns it.
     *
     * @param string $csvValue
     *
     * @return string|null
     */
    public function normalizeValue($csvValue)
    {
        $csvValue = trim($csvValue);

        if ($csvValue === '') {
            $returnValue = $this->getDefaultValue();
            if ($returnValue !== false) {
                return $returnValue;
            }
        }

        // if value is 'NULL' the current value should be deleted which is handled later
        if ($csvValue === 'NULL') {
            return $csvValue;
        }

        // try to get the value based on frontend input or backend type
        if ($this->_attribute->getFrontendInput() === 'select') {
            $returnValue = $this->_getSelectValue($csvValue);
        } elseif ($this->_attribute->getFrontendInput() === 'multiselect') {
            $optionIds = [];
            foreach (explode(',', $csvValue) as $value) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }

                $optionId = $this->_getSelectValue($value);
                if ($optionId !== null) {
                    $optionIds[] = $optionId;
                }
            }

            if (empty($optionIds)) {
                $returnValue = null;
            } else {
                $returnValue = implode(',', $optionIds);
            }
        } elseif ($this->_attribute->getFrontendInput() === 'boolean') {
            if ($this->_attribute->getSourceModel() === null) {
                $returnValue = $this->_getYesNoId($csvValue);
            } else {
                $returnValue = $this->_getSelectValue($csvValue);
                if ($returnValue === null) {
                    $returnValue = $this->_getYesNoId($csvValue);
                }
            }
        } else {
            switch ($this->_attribute->getBackendType()) {
                case 'datetime':
                    try {
                        $time = new DateTime($csvValue);
                    } catch (Exception $e) {
                        $time = DateTime::createFromFormat($this->_dateTimeFormat, $csvValue);
                    }

                    $returnValue = $time ? $time->format('Y-m-d H:i:s') : null;
                    break;
                case 'decimal':
                    $returnValue = (string)(float)str_replace(',', '.', $csvValue);
                    break;
                case 'int':
                    $returnValue = (string)(int)$csvValue;
                    break;
                case 'text':
                case 'varchar':
                default:
                    if (Fci_Helper_Config::getInstance()->getStripHtmlTags()) {
                        $csvValue = strip_tags($csvValue);
                    }

                    $returnValue = $csvValue;
            }
        }

        // if no value could be found get the default value
        if ($returnValue === null) {
            $returnValue = $this->getDefaultValue();
        }
        // if no default value either then result should be empty string
        if ($returnValue === false) {
            $returnValue = '';
        }

        return $returnValue;
    }

    /**
     * Returns the corresponding id for the given value of a select or multiselect attribute.
     * If the value does not exists it will be added.
     *
     * @param string $csvValue
     *
     * @return string|null
     */
    protected function _getSelectValue($csvValue)
    {
        $csvValueArr = explode('=>', $csvValue);
        if ($this->_importGlobally || count($csvValueArr) !== 2) {
            // get id of the value
            $optionId = $this->_attribute->getValueId($csvValue, $this->_attributeStoreViews);
            // if value is null the value is not in cache so create the value
            if ($optionId === null) {
                return $this->_createAttributeValue($csvValue);
            }

            return $optionId;
        }

        $adminOptionId = $this->_attribute->getValueId($csvValueArr[0], [0]);
        $storeOptionId = $this->_attribute->getValueId($csvValueArr[1], $this->_attributeStoreViews);

        if ($adminOptionId === null && $storeOptionId === null) {
            $adminOptionId = $this->_createAttributeValue($csvValueArr[0]);
            $storeOptionId = $this->_createAttributeValue($csvValueArr[1], $adminOptionId);
        } elseif ($adminOptionId === null) {
            $context = [
                'adminValue'    => $csvValueArr[0],
                'attributeCode' => $this->_attribute->getAttributeCode(),
            ];
            $this->_logger->warning(
                'Admin Attribute Wert "{adminValue}" für Attribut "{attributeCode}" fehlt.',
                $context
            );

            return null;
        } elseif ($storeOptionId === null) {
            $storeOptionId = $this->_createAttributeValue($csvValueArr[1], $adminOptionId);
        }

        return $storeOptionId;
    }

    /**
     * Returns the corresponding id for the given value of a yes/no attribute.
     *
     * @param string $csvValue
     *
     * @return string|null
     */
    private function _getYesNoId($csvValue)
    {
        $csvValue = strtolower($csvValue);
        if ($csvValue === 'ja' || $csvValue === 'yes' || $csvValue === '1') {
            return '1';
        }
        if ($csvValue === 'nein' || $csvValue === 'no' || $csvValue === '0') {
            return '0';
        }

        return null;
    }

    /**
     * Creates a value for the current select or multiselect attribute.
     *
     * @param string $value
     * @param null   $adminOptionId
     *
     * @return string|null
     */
    protected function _createAttributeValue($value, $adminOptionId = null)
    {
        $newValue = trim($value);
        if ($newValue === '') {
            return null;
        }
        if ($this->_attribute->getIsUserDefined() === 0) {
            return null;
        }
        if ($this->_attribute->getSourceModel() !== null
            && $this->_attribute->getSourceModel() !== 'Magento\Eav\Model\Entity\Attribute\Source\Table'
            && $this->_attribute->getSourceModel() !== 'eav/entity_attribute_source_table') {
            return null;
        }

        /** @var \Cemes\Pdo\Mysql $mysql */
        $mysql = Cemes_Registry::get('db');
        $value_ids = [];

        // global value
        if ($this->_attributeStoreViews[0] === 0) {
            $mysql->insert('eav_attribute_option')
                ->set('attribute_id', $this->_attribute->getAttributeId())
                ->query();
            $option_id = $mysql->lastInsertId();
        } else { // store specific value
            if ($adminOptionId === null) {
                return null;
            }
            $option_id = $adminOptionId;
            // get old value_ids in case we want to overwrite an old value
            $select = $mysql->select('eav_attribute_option_value', ['value_id', 'store_id'])
                ->where('option_id', $option_id);
            foreach ($select->fetchAll(null, PDO::FETCH_OBJ) as $row) {
                $value_ids[$row->store_id] = $row->value_id;
            }
        }
        foreach ($this->_attributeStoreViews as $storeView) {
            $replace = $mysql->replace('eav_attribute_option_value')
                ->set('option_id', $option_id)
                ->set('value', $newValue)
                ->set('store_id', $storeView);
            if (array_key_exists($storeView, $value_ids)) {
                $replace->set('value_id', $value_ids[$storeView]);
            }
            $replace->execute();
            $this->_cache->setData(
                'productAttributes/'
                . $this->_attribute->getAttributeCode()
                . '/values/'
                . $option_id
                . '/'
                . $storeView,
                $newValue
            );
        }

        $this->_cache->save();

        $context = [
            'value'         => $newValue,
            'attributeCode' => $this->_attribute->getAttributeCode(),
        ];
        $this->_logger->notice(
            'Attribute Wert "{value}" für Attribute "{attributeCode}" erfolgreich angelegt.',
            $context
        );

        return (string)$option_id;
    }

    /**
     * This method will return the default value from the config if one is set.
     * If not it will try to return the default value of the attribute if there is one.
     * If the attribute is not required we will just return null.
     * If no default value could be found but the value is required this method will return false.
     *
     * @return bool|null|string
     */
    protected function getDefaultValue()
    {
        if (array_key_exists($this->_attribute->getAttributeCode(), $this->_defaults)) {
            return $this->_defaults[$this->_attribute->getAttributeCode()];
        }
        if ($this->_attribute->getDefaultValue() !== false) {
            return $this->_attribute->getDefaultValue();
        }
        if (!$this->_attribute->getIsRequired()) {
            return null;
        }

        return false;
    }
}
