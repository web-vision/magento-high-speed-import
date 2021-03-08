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
class Fci_Helper_Config
{
    /**
     * @var $this
     */
    protected static $_instance;

    /**
     * @var Cemes_Helper_Config
     */
    protected $_config;

    /**
     * @var Fci_Log_LoggerInterface
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $_websites;

    /**
     * @var array
     */
    protected $_stores;

    /**
     * @var array
     */
    protected $_mappings;

    /**
     * @var array
     */
    protected $_mandatoryFields;

    /**
     * @var Fci_Helper_Exception[]
     */
    protected $_mandatoryExceptions;

    /**
     * @var Fci_Objects_AbstractScript[]
     */
    protected $_scripts;

    /**
     * @var string
     */
    protected $_importType;

    /**
     * Fci_Helper_Config constructor.
     */
    private function __construct()
    {
        $this->_logger = Cemes_Registry::get('logger');
    }

    /**
     * Private clone method to prevent cloning the object.
     */
    private function __clone()
    {
    }

    /**
     * Returns the instance of this class.
     *
     * @return Fci_Helper_Config
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    /**
     * Loads the given config file in the bin folder and adds the magento mysql configuration to it.
     *
     * @param string $configFile
     */
    public function load($configFile)
    {
        /** @var Fci_Log_LoggerInterface $logger */
        $logger = Cemes_Registry::get('logger');

        // load config for fci
        $this->_config = Cemes::loadModule('Helper/Config')
            ->setPath(ROOT_BASEDIR . 'bin/');
        // if config is false the config couldn't be read
        if ($this->_config->load($configFile) === false) {
            $context = ['configFile' => $configFile];
            $logger->critical(
                'Can\'t read configuration.' . PHP_EOL . 'Path "bin/{configFile}" may be wrong.',
                $context
            );
        }

        // load MySQL config from Magento
        $mysqlConf = Fci_Helper_Factory_MagentoFactory::getMagento()->getDatabaseConfig();

        // cleanup cdata
        $this->_config->set('mysql/host', $mysqlConf['host']);
        $this->_config->set('mysql/username', $mysqlConf['username']);
        $this->_config->set('mysql/password', $mysqlConf['password']);
        $this->_config->set('mysql/dbname', $mysqlConf['dbname']);
        $this->_config->set('mysql/charset', 'utf8');
    }

    // ========== file ==========

    /**
     * Returns the path to the file that should be imported.
     *
     * @return string
     */
    public function getFilePath()
    {
        $value = $this->_config->get('file/path');

        if ($value === null) {
            $value = '../var/import/import_all_products.csv';
        }

        return $value;
    }

    /**
     * Returns the delimiter for the csv file.
     *
     * @return string
     */
    public function getFileDelimiter()
    {
        $value = $this->_config->get('file/delimiter');

        if ($value === null) {
            $value = ';';
        }

        return $value;
    }

    /**
     * Returns the enclosure for the csv file.
     *
     * @return string
     */
    public function getFileEnclosure()
    {
        $value = $this->_config->get('file/enclosure');

        if ($value === null) {
            $value = '"';
        }

        return $value;
    }

    /**
     * Returns if the file should be archived with a date time suffix.
     *
     * @return bool
     */
    public function getArchiveWithDateTime()
    {
        $value = $this->_config->get('file/archive_with_datetime');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    // ========== general ==========

    /**
     * Returns if the cache should be reloaded at the beginning of the import.
     *
     * @return bool
     */
    public function getReloadCache()
    {
        $value = $this->_config->get('general/reload_cache');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    /**
     * Returns if all products should be deactivated at the beginning of the import.
     *
     * @return bool
     */
    public function getDisableProducts()
    {
        $value = $this->_config->get('general/disable_products');

        if ($value === null) {
            $value = false;
        }

        return (bool)$value && $this->getMode() === 'products';
    }

    /**
     * Returns if the disabled products should be deleted at the end of the import.
     *
     * @return bool
     */
    public function getDeleteDisabledProducts()
    {
        $value = $this->_config->get('general/delete_disabled_products');

        if ($value === null) {
            $value = false;
        }

        return (bool)$value && $this->getMode() === 'products';
    }

    /**
     * Returns additional conditions to add to the disable product query.
     *
     * @return array
     */
    public function getDisableConditions()
    {
        $value = $this->_config->get('general/disable_conditions');

        if ($value === null) {
            $value = [];
        }

        return $value;
    }

    /**
     * Returns if all products that are not in the csv should be deleted at the end of the import.
     *
     * @return bool
     */
    public function getDeleteProductsNotInCsv()
    {
        $value = $this->_config->get('general/delete_products_not_in_csv');

        if ($value === null) {
            $value = false;
        }

        return (bool)$value && $this->getMode() === 'products';
    }

    /**
     * Returns if special price per product should be unset
     *
     * @return bool
     */
    public function getUnsetSpecialPrice()
    {
        $value = $this->_config->get('general/unset_special_price');

        if ($value === null) {
            $value = false;
        }

        return (bool)$value && $this->getMode() === 'products';
    }

    /**
     * Returns if all special prices should be deleted
     *
     * @return bool
     */
    public function getDeleteAllSpecialPrices()
    {
        $value = $this->_config->get('general/delete_all_special_prices');

        if ($value === null) {
            $value = false;
        }

        return (bool)$value && $this->getMode() === 'products';
    }

    /**
     * Returns the defined scripts.
     *
     * @return Fci_Objects_AbstractScript[]
     */
    public function getScripts()
    {
        if ($this->_scripts === null) {
            $this->_scripts = [];

            $scripts = $this->_config->get('general/scripts');
            if (!is_array($scripts)) {
                return $this->_scripts;
            }

            foreach ($scripts as $scriptName => $params) {
                if (is_file(ROOT_BASEDIR . 'bin/scripts/' . $scriptName . '.php')) {
                    include_once ROOT_BASEDIR . 'bin/scripts/' . $scriptName . '.php';

                    if (!class_exists($scriptName)) {
                        $context = ['name' => $scriptName];
                        $this->_logger->error('Script mit dem Klassennamen {name} existiert nicht.', $context);
                        continue;
                    }

                    if (!is_subclass_of($scriptName, 'Fci_Objects_AbstractScript')) {
                        $context = ['name' => $scriptName];
                        $this->_logger->error(
                            'Script mit dem Klassennamen {name} muss Fci_Objects_AbstractScript erweitern.',
                            $context
                        );
                        continue;
                    }

                    try {
                        $script = call_user_func_array(
                            [new ReflectionClass($scriptName), 'newInstance'],
                            [$params]
                        );
                        $this->_scripts[$scriptName] = $script;
                    } catch (ReflectionException $e) {
                        $this->_logger->error($e->getMessage());
                    }
                }
            }
        }

        return $this->_scripts;
    }

    // ========== dataprocessing/general ==========

    /**
     * Returns the mode which defines if products or categories are imported.
     *
     * @return string
     */
    public function getMode()
    {
        $value = $this->_config->get('dataprocessing/general/mode');

        if ($value === null) {
            $value = 'products';
        }

        return $value;
    }

    /**
     * Returns the import type which defines if entities should be inserted, updated or both.
     *
     * @return string
     */
    public function getImportType()
    {
        $value = $this->_config->get('dataprocessing/general/import');

        if ($this->_importType !== null) {
            return $this->_importType;
        }

        if ($value === null) {
            $value = 'both';
        }

        return $value;
    }

    /**
     * Sets the import type for the current run.
     *
     * @param string $value
     */
    public function setImportType($value)
    {
        switch ($value) {
            case 'insert':
            case 'update':
            case 'both':
            case 'none':
                $this->_importType = $value;
        }
    }

    /**
     * Returns the amount of lines that should be read and processed together.
     *
     * @return int
     */
    public function getLinesToCache()
    {
        $value = $this->_config->get('dataprocessing/general/cache_lines');

        if ($value === null) {
            $value = 200;
        }

        return (int)$value;
    }

    /**
     * Returns the value of the mappings node.
     *
     * @return string
     */
    public function getMappingsValue()
    {
        $value = $this->_config->get('dataprocessing/general/mappings');

        if ($value === null) {
            $value = 'none';
        }

        return $value;
    }

    /**
     * Returns the mappings.
     *
     * @return array
     */
    public function getMappings()
    {
        if ($this->_mappings === null) {
            $this->_mappings = [];

            $mappings = $this->_config->get('dataprocessing/general/mappings');
            if ($mappings && $mappings !== 'none') {
                /** @var Cemes_Helper_Config $_mappings */
                $_mappings = Cemes::loadModule('Helper/Config')
                    ->setPath(ROOT_BASEDIR . 'bin/mappings');
                $_mappings->load($mappings);
                if ($_mappings->count() === 0) {
                    $this->_logger->critical('Couldn\'t load mappings. Please check your configuration.');
                }

                $doublets = [];
                foreach ($_mappings->get('') as $key => $value) {
                    if (is_array($value)) {
                        $doublets[] = $key;
                    }
                }
                if (count($doublets)) {
                    $context = ['doublets' => implode(', ', $doublets)];
                    $this->_logger->critical(
                        'Mapping contains doublets for key {doublets}. Please check your configuration.',
                        $context
                    );
                }

                $this->_mappings = $_mappings->get('');
            }
        }

        return $this->_mappings;
    }

    /**
     * Returns if html tags should be stripped from varchar and text attributes.
     *
     * @return bool
     */
    public function getStripHtmlTags()
    {
        $value = $this->_config->get('dataprocessing/general/strip_html_tags');

        if ($value === null) {
            $value = false;
        }

        return (bool)$value;
    }

    /**
     * Returns if data should be imported globally or not.
     *
     * @return bool
     */
    public function getImportGlobally()
    {
        $value = $this->_config->get('dataprocessing/general/import_globally');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    /**
     * Returns the date time format if the format used in the csv is not known to PHP.
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        $value = $this->_config->get('dataprocessing/general/date_time_format');

        if ($value === null) {
            $value = 'Y.m.d H:i:s';
        }

        return $value;
    }

    // ========== dataprocessing/images ==========

    /**
     * Returns the prefix for images.
     *
     * @return string
     */
    public function getImagePrefix()
    {
        $value = $this->_config->get('dataprocessing/images/image_prefix');

        if ($value === null) {
            $value = '';
        }

        return $value;
    }

    /**
     * Returns the separator for multiple images.
     *
     * @return string
     */
    public function getImageSeparator()
    {
        $value = $this->_config->get('dataprocessing/images/image_separator');

        if ($value === null) {
            $value = ',';
        }

        return $value;
    }

    /**
     * Returns if the sku should be used as a fallback if no image, small_image or thumbnail is found.
     *
     * @return bool
     */
    public function getUseSkuImageFallback()
    {
        $value = $this->_config->get('dataprocessing/images/sku_fallback');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    /**
     * Returns if the gallery should be imported.
     *
     * @return bool
     */
    public function getImportGallery()
    {
        $value = $this->_config->get('dataprocessing/images/import_gallery');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    // ========== dataprocessing/mandatory ==========

    /**
     * Returns the mandatory fields.
     *
     * @return array
     */
    public function getMandatoryFields()
    {
        if ($this->_mandatoryFields === null) {
            $this->_mandatoryFields = [];
            $mandatoryFields = $this->_config->get('dataprocessing/mandatory/fields');

            if (is_array($mandatoryFields)) {
                foreach ($mandatoryFields as $fieldName => $empty) {
                    $this->_mandatoryFields[] = $fieldName;
                }
            }
        }

        return $this->_mandatoryFields;
    }

    /**
     * Returns the exceptions for mandatory fields.
     *
     * @return Fci_Helper_Exception[]
     */
    public function getMandatoryExceptions()
    {
        if ($this->_mandatoryExceptions === null) {
            $this->_mandatoryExceptions = [];

            $exceptions = $this->_config->get('dataprocessing/mandatory/exceptions');

            if (!empty($exceptions)) {
                if (is_array($exceptions['exception']) && is_int(key($exceptions['exception']))) {
                    foreach ($exceptions['exception'] as $exception) {
                        $this->_mandatoryExceptions[] = Cemes::loadModule('Helper/Exception', [$exception], 'Fci');
                    }
                } else {
                    $this->_mandatoryExceptions[] = Cemes::loadModule(
                        'Helper/Exception',
                        [$exceptions['exception']],
                        'Fci'
                    );
                }
            }
        }

        return $this->_mandatoryExceptions;
    }

    // ========== dataprocessing/products ==========

    /**
     * Returns the identifier for products.
     *
     * @return string
     */
    public function getProductIdentifier()
    {
        $value = $this->_config->get('dataprocessing/products/identifier');

        if ($value === null) {
            $value = 'sku';
        }

        return $value;
    }

    /**
     * Returns if the existing product website relations should be cleared.
     *
     * @return bool
     */
    public function getClearExistingWebsites()
    {
        $value = $this->_config->get('dataprocessing/products/clear_existing_websites');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    // ========== general_defaults ==========

    /**
     * Returns the value for websites of the general defaults as an array.
     *
     * @return array
     */
    public function getWebsitesValue()
    {
        $value = $this->_config->get('general_defaults/websites');

        if ($value === null) {
            $values = $this->_cleanWebsites([]);
            $value = key($values);
        }

        $value = explode(',', $value);
        $value = array_map('trim', $value);
        $value = array_filter($value);

        return $value;
    }

    /**
     * Returns the value for websites of the general defaults as an array.
     *
     * @return array
     */
    public function getStoreValue()
    {
        $value = $this->_config->get('general_defaults/store');

        if ($value === null) {
            $value = '0';
        }

        $value = explode(',', $value);
        $value = array_map('trim', $value);
        $value = array_filter($value, function($value) {
            return $value || $value === '0';
        });

        return $value;
    }

    // ========== product_defaults ==========

    /**
     * Get the defaults for products.
     *
     * @return array
     */
    public function getProductDefaults()
    {
        $defaults = $this->_config->get('product_defaults');

        if (is_array($defaults)) {
            return $defaults;
        }

        return [];
    }

    /**
     * Returns the product default with the given needle or null if not present.
     *
     * @param $needle
     *
     * @return string
     */
    public function getProductDefault($needle)
    {
        $defaults = $this->getProductDefaults();

        if (array_key_exists($needle, $defaults)) {
            return $defaults[$needle];
        }

        return null;
    }

    // ========== category_defaults ==========

    /**
     * Get the defaults for categories.
     *
     * @return array
     */
    public function getCategoryDefaults()
    {
        $defaults = $this->_config->get('category_defaults');

        if (is_array($defaults)) {
            return $defaults;
        }

        return [];
    }

    /**
     * Returns the product default with the given needle or null if not present.
     *
     * @param $needle
     *
     * @return string
     */
    public function getCategoryDefault($needle)
    {
        $defaults = $this->getCategoryDefaults();

        if (array_key_exists($needle, $defaults)) {
            return $defaults[$needle];
        }

        return null;
    }

    // ========== category_settings ==========

    /**
     * Returns the root category set in the defaults.
     *
     * @return int
     */
    public function getRootCategory()
    {
        $value = $this->_config->get('category_settings/root_category');

        if ($value === null) {
            $value = 2;
        }

        return (int)$value;
    }

    /**
     * Returns the separator to separate multiple categories form each other.
     *
     * @return string
     */
    public function getCategorySeparate()
    {
        $value = $this->_config->get('category_settings/category_separate');

        if ($value === null) {
            $value = ',';
        }

        return $value;
    }

    /**
     * Returns the separator to separate sub categories from each other.
     *
     * @return string
     */
    public function getSubCategorySeparate()
    {
        $value = $this->_config->get('category_settings/sub_category_separate');

        if ($value === null) {
            $value = '#';
        }

        return $value;
    }

    /**
     * If creating categories is allowed or not.
     *
     * @return bool
     */
    public function createCategories()
    {
        $value = $this->_config->get('category_settings/create_categories');

        if ($value === null) {
            $value = true;
        }

        return (bool)$value;
    }

    /**
     * Returns the default product position inside a category.
     *
     * @return int
     */
    public function getDefaultProductPosition()
    {
        $value = $this->_config->get('category_settings/default_product_position');

        if ($value === null) {
            $value = 1;
        }

        return (int)$value;
    }

    /**
     * Fallback method to get data from the inner config file.
     *
     * @param string $key
     * @param string $section
     *
     * @return mixed
     */
    public function get($key, $section = '')
    {
        return $this->_config->get($key, $section);
    }

    /**
     * Fallback method to set data in the inner config file.
     *
     * @param string $key
     * @param mixed  $value
     * @param string $section
     */
    public function set($key, $value, $section = '')
    {
        $this->_config->set($key, $value, $section);
    }

    /**
     * Returns the website ids from the configuration. If $entityData is provided it can overwrite the configuration.
     *
     * @param array $entityData [optional] An array with product or category data.
     *
     * @return array
     */
    public function getWebsites($entityData = null)
    {
        // if entity data holds websites clean the data and return it
        if ($entityData && array_key_exists('websites', $entityData)) {
            $websites = $entityData['websites'];
            $websites = explode(',', $websites);
            $websites = array_map('trim', $websites);
            $websites = array_filter($websites);

            return $this->_cleanWebsites($websites);
        }

        // if websites are not retrieved yet get the websites value from config and clean it
        if (!$this->_websites) {
            $websites = $this->_config->get('general_defaults/websites');
            $websites = explode(',', $websites);
            $websites = array_map('trim', $websites);
            $websites = array_filter($websites);

            $this->_websites = $this->_cleanWebsites($websites);
        }

        return $this->_websites;
    }

    /**
     * Checks if the given values are either the website id or the website code of a magento website.
     * If no value is valid this will return the first website that is not admin as a fallback.
     *
     * @param array $websites
     *
     * @return array
     */
    protected function _cleanWebsites($websites)
    {
        // check if website exists in magento as id or as code
        $websiteIds = [];
        $cacheWebsites = Fci_Objects_Cache::getInstance()->getData('websites');
        foreach ($websites as $website) {
            if (is_numeric($website) && array_key_exists($website, $cacheWebsites)) {
                $websiteIds[] = (int)$website;
            } elseif (!is_numeric($website)) {
                foreach ($cacheWebsites as $websiteId => $cacheWebsite) {
                    if ($cacheWebsite['code'] === $website) {
                        $websiteIds[] = $websiteId;
                        break;
                    }
                }
            }
        }

        // if no website id could be found use the first website that is not admin
        if (!$websiteIds) {
            foreach ($cacheWebsites as $websiteId => $cacheWebsite) {
                if ($websiteId > 0) {
                    $websiteIds[] = $websiteId;
                    break;
                }
            }
        }

        return $websiteIds;
    }

    /**
     * Returns the store ids from the configuration. If $entityData is provided it can overwrite the configuration.
     *
     * @param array $entityData [optional] An array with product or category data.
     *
     * @return array
     */
    public function getStores($entityData = null)
    {
        // if entity data holds stores, clean the data and return it
        if ($entityData && array_key_exists('store', $entityData)) {
            $stores = $entityData['store'];
            $stores = explode(',', $stores);
            $stores = array_map('trim', $stores);
            $stores = array_filter($stores);

            return $this->_cleanStores($stores, $entityData);
        }

        // if stores are not retrieved yet, get the stores value from config and clean it
        if (!$this->_stores) {
            $stores = $this->_config->get('general_defaults/store');
            $stores = explode(',', $stores);
            $stores = array_map('trim', $stores);
            $stores = array_filter($stores);

            $this->_stores = $this->_cleanStores($stores, $entityData);
        }

        return $this->_stores;
    }

    /**
     * Checks if the given values are either the store view id or the store view code of a magento store view.
     * If no value is valid or stores was an empty array this will return all store view ids assigned to the used
     * websites.
     *
     * @param array $stores
     * @param array $entityData
     *
     * @return array
     */
    protected function _cleanStores($stores, $entityData)
    {
        $tmpStoreViewIds = [];
        $cleanedStoreViewIds = [];
        $websiteIds = $this->getWebsites($entityData);

        // check if website exists in magento as id or as code
        $cacheStores = Fci_Objects_Cache::getInstance()->getData('store');
        foreach ($stores as $store) {
            if (is_numeric($store) && array_key_exists($store, $cacheStores)) {
                $tmpStoreViewIds[] = (int)$store;
            } elseif (!is_numeric($store)) {
                foreach ($cacheStores as $storeId => $cacheStore) {
                    if ($cacheStore['code'] === $store) {
                        $tmpStoreViewIds[] = $storeId;
                        break;
                    }
                }
            }
        }

        foreach ($websiteIds as $websiteId) {
            /** @var array $storeViews */
            $storeViews = Fci_Objects_Cache::getInstance()->getData('websites/' . $websiteId . '/stores');
            foreach ($storeViews as $storeViewId) {
                if (!$tmpStoreViewIds || in_array($storeViewId, $tmpStoreViewIds, true)) {
                    $cleanedStoreViewIds[] = $storeViewId;
                }
            }
        }

        return array_unique($cleanedStoreViewIds);
    }
}
