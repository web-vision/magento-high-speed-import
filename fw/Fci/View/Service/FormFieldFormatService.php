<?php

namespace Fci\View\Service;

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
class FormFieldFormatService
{
    /**
     * Formats the data and adds all possible values.
     *
     * @param $data
     */
    public function formatField(&$data)
    {
        $data['file']['delimiter'] = $this->_formatDelimiterSelect($data);
        $data['file']['enclosure'] = $this->_formatEnclosureSelect($data);

        $data['general']['scripts'] = $this->formatScriptFields($data, true);

        $data['dataprocessing']['general']['mode'] = $this->_formatModeSelect(
            $data['dataprocessing']['general']['mode']
        );
        $data['dataprocessing']['general']['import'] = $this->_formatImportSelect(
            $data['dataprocessing']['general']['import']
        );
        $data['dataprocessing']['images']['image_prefix'] = $this->_formatImagesPrefix($data);
        $data['general_defaults']['websites'] = $this->_formatWebsitesFields($data['general_defaults']['websites']);
        $data['general_defaults']['store'] = $this->_formatStoreFields($data['general_defaults']['store']);

        $configServices = new ConfigService('bin/mappings');
        $mappingFiles = $configServices->getFiles();

        $data['dataprocessing']['general']['mappings'] = $this->_formatMappingSelect(
            $data,
            $mappingFiles
        );
    }

    /**
     * Creates an array with the allowed values for the enclosure and sets selected to true if the value is given in
     * $data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _formatEnclosureSelect($data)
    {
        $enclosure = [
            ['value' => '"', 'selected' => false],
            ['value' => '\'', 'selected' => false],
        ];

        switch ($data['config']['file']['enclosure']) {
            case '"':
                $enclosure[0]['selected'] = true;
                break;
            case '\'':
                $enclosure[1]['selected'] = true;
                break;
        }

        return $enclosure;
    }

    /**
     * Creates an array with the allowed values for the delimiter and sets selected to true if the value is given in
     * $data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _formatDelimiterSelect($data)
    {
        $delimiter = [
            ['value' => ';', 'selected' => false],
            ['value' => ',', 'selected' => false],
            ['value' => '|', 'selected' => false],
        ];
        if (isset($data['file']['delimiter'])) {
            switch ($data['file']['delimiter']) {
                case ';':
                    $delimiter[0]['selected'] = true;
                    break;
                case ',':
                    $delimiter[1]['selected'] = true;
                    break;
                case '|':
                    $delimiter[2]['selected'] = true;
                    break;
            }
        }

        return $delimiter;
    }

    /**
     * Creates an array with the allowed values for the mappings and sets selected to true if the value is given in
     * $data.
     *
     * @param array $data
     * @param array $mappingFiles
     *
     * @return array
     */
    protected function _formatMappingSelect($data, $mappingFiles)
    {
        $mappings = [
            [
                'value'    => 'none',
                'label'    => 'No mapping',
                'selected' => false,
            ],
        ];

        foreach ($mappingFiles as $mappingFile) {
            $mapping = [
                'value'    => $mappingFile['basename'],
                'label'    => $mappingFile['name'],
                'selected' => $data['dataprocessing']['general']['mappings'] === $mappingFile['basename'],
            ];
            $mappings[] = $mapping;
        }

        return $mappings;
    }

    /**
     * Cleans the images prefix value as it can be an empty array if the value is empty.
     *
     * @param array $data
     *
     * @return string
     */
    protected function _formatImagesPrefix($data)
    {
        $res = $data['config']['dataprocessing']['images']['image_prefix'];

        if (is_array($res)) {
            $res = '';
        }

        return $res;
    }

    /**
     * Creates an array with the allowed values for the scripts and sets selected to true if the value is given in
     * $data and $forView is true.
     *
     * @param array $data
     * @param bool  $forView
     *
     * @return array
     */
    public function formatScriptFields($data, $forView = false)
    {
        $scripts = [];
        foreach ($data['general']['scripts'] as $key => $script) {
            $normalizedScript = $script;
            if ($script instanceof \Fci_Objects_AbstractScript) {
                $normalizedScript = $script->getConfiguration();
            }

            if ($forView) {
                $normalizedScript['event'] = $this->formatScriptEventSelect($normalizedScript['event']);
            }

            $scripts[$key] = $normalizedScript;
        }

        return $scripts;
    }

    /**
     * Creates an array with the allowed values for the event dropdown and sets selected to true if the value is given
     * in $scriptEvent.
     *
     * @param string $scriptEvent
     *
     * @return array
     */
    public function formatScriptEventSelect($scriptEvent = '')
    {
        $events = [
            ['value' => 'before_csv_check', 'selected' => false],
            ['value' => 'after_csv_check', 'selected' => false],
            ['value' => 'after_database_initialization', 'selected' => false],
            ['value' => 'after_cache_initialization', 'selected' => false],
            ['value' => 'before_csv_processing', 'selected' => false],
            ['value' => 'after_csv_processing', 'selected' => false],
        ];

        foreach ($events as &$event) {
            if ($event['value'] === $scriptEvent) {
                $event['selected'] = true;
            }
        }

        return $events;
    }

    /**
     * Creates an array with the allowed values for the mode and sets selected to true if the value is given in
     * $configMode.
     *
     * @param string $configMode
     *
     * @return array
     */
    protected function _formatModeSelect($configMode = '')
    {
        $modes = [
            ['value' => 'products', 'selected' => false],
            ['value' => 'categories', 'selected' => false],
        ];

        foreach ($modes as &$mode) {
            if ($mode['value'] === $configMode) {
                $mode['selected'] = true;
            }
        }

        return $modes;
    }

    /**
     * Creates an array with the allowed values for the import and sets selected to true if the value is given in
     * $configImport.
     *
     * @param string $configImport
     *
     * @return array
     */
    protected function _formatImportSelect($configImport = '')
    {
        $imports = [
            ['value' => 'both', 'selected' => false],
            ['value' => 'import', 'selected' => false],
            ['value' => 'update', 'selected' => false],
        ];

        foreach ($imports as &$import) {
            if ($import['value'] === $configImport) {
                $import['selected'] = true;
            }
        }

        return $imports;
    }

    /**
     * Creates an array with the allowed values for the website and sets selected to true if the value is given in
     * $configWebsite.
     *
     * @param array $configWebsite
     *
     * @return array
     */
    protected function _formatWebsitesFields($configWebsite)
    {
        $cache = \Fci_Objects_Cache::getInstance();

        $websites = $cache->getData('websites');

        array_walk(
            $websites,
            function (&$website, $websiteId, $selected) {
                $website['value'] = $websiteId;
                $website['label'] = $website['name'];
                $website['selected'] = in_array($websiteId, $selected, false);
            },
            $configWebsite
        );

        // value 0 is not allowed for website
        unset($websites[0]);

        return $websites;
    }

    /**
     * Creates an array with the allowed values for the store and sets selected to true if the value is given in
     * $configStores.
     *
     * @param array $configStores
     *
     * @return array
     */
    protected function _formatStoreFields($configStores)
    {
        $cache = \Fci_Objects_Cache::getInstance();

        $stores = $cache->getData('store');

        array_walk(
            $stores,
            function (&$store, $storeId, $selected) {
                $store['value'] = $storeId;
                $store['label'] = $store['name'];
                $store['selected'] = in_array($storeId, $selected, false);
            },
            $configStores
        );

        // special case as 0 in FE is not the admin website but global scope
        $stores[0]['label'] = 'Global';

        return $stores;
    }
}
