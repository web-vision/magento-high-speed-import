<?php

namespace Fci\View\Service;

use Cemes\Parser\ParserException;
use DirectoryIterator;

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
 * @package     Fci\View\Controller
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Mark Houben <mark.houben@wmdb.de>
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class ConfigService
{
    /**
     * @var string
     */
    protected $_configPath;

    /**
     * @var array
     */
    protected $_configFiles = [];

    /**
     * ConfigService constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        $this->_configPath = realpath(ROOT_BASEDIR . $path) . DIRECTORY_SEPARATOR;

        $this->_findConfigs();
    }

    /**
     * Reads all current files of the config folder.
     */
    protected function _findConfigs()
    {
        foreach (new DirectoryIterator($this->_configPath) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($fileInfo->isDir()) {
                continue;
            }
            if ($fileInfo->getExtension() !== 'xml') {
                continue;
            }

            $name = str_replace('.' . $fileInfo->getExtension(), '', $fileInfo->getFilename());
            $this->_configFiles[$name] = [
                'name'     => $name,
                'basename' => $fileInfo->getBasename(),
                'pathname' => $fileInfo->getPathname(),
            ];
        }
    }

    /**
     * Returns the previously fetched list of all files in the config folder.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->_configFiles;
    }

    /**
     * Returns the file path of either an existing file or the file path for a new file based on the path of this
     * service.
     *
     * @param $file
     *
     * @return string
     */
    public function getFilePath($file)
    {
        if (array_key_exists($file, $this->_configFiles)) {
            $this->_configFiles[$file]['pathname'];
        }

        if (!pathinfo($file, PATHINFO_EXTENSION)) {
            $file .= '.xml';
        }

        return $this->_configPath . $file;
    }

    /**
     * Deletes the given file.
     *
     * @param string $file
     */
    public function delete($file)
    {
        $file = $this->getFilePath($file);

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Loads the given file. If the file does not exists or the file name is empty the default.xml.dist will be loaded.
     * The values of this config file will than be returned in a multidimensional array.
     *
     * @param string $fileName
     *
     * @return array
     */
    public function getDefaultConfig($fileName = null)
    {
        $configHelper = \Fci_Helper_Config::getInstance();
        if (!$fileName) {
            $configHelper->load('default.xml.dist');
        } else {
            try {
                $configHelper->load($fileName . '.xml');
            } catch (ParserException $e) {
                $configHelper->load('default.xml.dist');
            }
        }

        $default = [
            'file'              => [
                'path'                  => $configHelper->getFilePath(),
                'delimiter'             => $configHelper->getFileDelimiter(),
                'enclosure'             => $configHelper->getFileEnclosure(),
                'archive_with_datetime' => (int)$configHelper->getArchiveWithDateTime(),
            ],
            'general'           => [
                'reload_cache'               => (int)$configHelper->getReloadCache(),
                'disable_products'           => (int)$configHelper->getDisableProducts(),
                'delete_disabled_products'   => (int)$configHelper->getDeleteDisabledProducts(),
                'disable_conditions'         => $configHelper->getDisableConditions(),
                'delete_products_not_in_csv' => (int)$configHelper->getDeleteProductsNotInCsv(),
                'unset_special_price'        => (int)$configHelper->getUnsetSpecialPrice(),
                'delete_all_special_prices'   => (int)$configHelper->getDeleteAllSpecialPrices(),
                'scripts'                    => $configHelper->getScripts(),
            ],
            'dataprocessing'    => [
                'general'   => [
                    'mode'             => $configHelper->getMode(),
                    'import'           => $configHelper->getImportType(),
                    'cache_lines'      => $configHelper->getLinesToCache(),
                    'mappings'         => $configHelper->getMappingsValue(),
                    'strip_html_tags'  => (int)$configHelper->getStripHtmlTags(),
                    'import_globally'  => (int)$configHelper->getImportGallery(),
                    'date_time_format' => $configHelper->getDateTimeFormat(),
                ],
                'images'    => [
                    'image_prefix'   => $configHelper->getImagePrefix(),
                    'image_split'    => $configHelper->getImageSeparator(),
                    'sku_fallback'   => (int)$configHelper->getUseSkuImageFallback(),
                    'import_gallery' => (int)$configHelper->getImportGallery(),
                ],
                'mandatory' => $configHelper->getMandatoryFields(),
                'products'  => [
                    'identifier'              => $configHelper->getProductIdentifier(),
                    'clear_existing_websites' => (int)$configHelper->getClearExistingWebsites(),
                ],
            ],
            'general_defaults'  => [
                'websites' => $configHelper->getWebsitesValue(),
                'store'    => $configHelper->getStoreValue(),
            ],
            'product_defaults'  => $configHelper->getProductDefaults(),
            'category_defaults' => $configHelper->getCategoryDefaults(),
            'category_settings' => [
                'root_category'            => $configHelper->getRootCategory(),
                'category_separate'        => $configHelper->getCategorySeparate(),
                'sub_category_separate'    => $configHelper->getSubCategorySeparate(),
                'create_categories'        => (int)$configHelper->createCategories(),
                'default_product_position' => $configHelper->getDefaultProductPosition(),
            ],
        ];

        return $default;
    }

    /**
     * Returns the path set up for this instance.
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->_configPath;
    }
}
