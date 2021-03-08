<?php

namespace Fci\View\Controller;

use Cemes\Parser\ParserFactory;
use Fci\View\Service\ConfigService;
use Fci\View\Service\RequestService;
use Fci\View\Service\ResponseService;

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
 */
class MappingController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function indexAction(RequestService $requestService, ResponseService $responseService)
    {
        $data = $this->_view->getDataArray();

        $productAttributes = \Fci_Objects_Cache::getInstance()->getData('productAttributes');

        $products = [];
        foreach ($productAttributes as $productAttribute) {
            $products[] = [
                'value'    => $productAttribute['attribute_code'],
                'label'    => $productAttribute['label'],
                'selected' => false,
            ];
        }
        $data['products'] = $products;
        $data['filename'] = '';
        $data['mappings'] = '';

        return $responseService->setContent(
            $this->_view->render(
                'Pages/Mapping.twig',
                $data
            )
        );
    }

    /**
     * Action to update a mapping.
     *
     * @param \Fci\View\Service\RequestService  $requestService
     * @param \Fci\View\Service\ResponseService $responseService
     *
     * @return \Fci\View\Service\ResponseService
     */
    public function updateAction(RequestService $requestService, ResponseService $responseService)
    {

        $data = $this->_view->getDataArray();
        $gp = $requestService->getGetPost();

        $configFile = $this->_getMappingFile($requestService);
        $configServices = new ConfigService('bin/mappings');
        $mappingFileInformation = $configServices->getFiles()[$configFile];

        $data['filename'] = $mappingFileInformation['name'];
        if ($gp['view']['type'] === 'clone') {
            $data['filename'] = '';
        }

        $mappingConfig = ParserFactory::read($mappingFileInformation['pathname']);
        $productAttributes = \Fci_Objects_Cache::getInstance()->getData('productAttributes');

        $data['mappings'] = [];
        foreach ($mappingConfig as $csv => $attr) {
            $products = [];
            foreach ($productAttributes as $productKey => $productAttribute) {
                if ($productKey == $attr) {
                    $products[] = [
                        'value'    => $productAttribute['attribute_code'],
                        'label'    => $productAttribute['label'],
                        'selected' => true,
                    ];
                } else {
                    $products[] = [
                        'value'    => $productAttribute['attribute_code'],
                        'label'    => $productAttribute['label'],
                        'selected' => false,
                    ];
                }
            }

            $data['mappings'][$csv]['csv'] = str_replace('_', ' ', $csv);
            $data['mappings'][$csv]['products'] = $products;
        }

        $products = [];
        foreach ($productAttributes as $productAttribute) {
            $products[] = [
                'value'    => $productAttribute['attribute_code'],
                'label'    => $productAttribute['label'],
                'selected' => false,
            ];
        }
        $data['products'] = $products;

        return $responseService->setContent(
            $this->_view->render(
                'Pages/Mapping.twig',
                $data
            )
        );
    }

    /**
     * Action to clone a mapping.
     *
     * @param \Fci\View\Service\RequestService  $requestService
     * @param \Fci\View\Service\ResponseService $responseService
     *
     * @return ResponseService
     */
    public function cloneAction(RequestService $requestService, ResponseService $responseService)
    {
        $gp = $requestService->getGetPost();
        $fileName = '';
        if ($gp['config']['mapping']['file']) {
            $fileName = $gp['config']['mapping']['file'];
        }

        return $responseService->redirect(
            'mapping',
            'update',
            [
                'mapping' => $fileName,
                'type'    => 'clone',
            ]
        );
    }

    /**
     * Action to save a mapping.
     *
     * @param \Fci\View\Service\RequestService  $requestService
     * @param \Fci\View\Service\ResponseService $responseService
     *
     * @return \Fci\View\Service\ResponseService
     */
    public function saveAction(RequestService $requestService, ResponseService $responseService)
    {
        $gp = $requestService->getGetPost();

        $gp['mapping']['csv'] = array_map(
            function ($value) {
                return str_replace(' ', '_', $value);
            },
            $gp['mapping']['csv']
        );

        $mappings = array_combine($gp['mapping']['csv'], $gp['mapping']['attr']);

        $configServices = new ConfigService('bin/mappings');
        $path = $configServices->getConfigPath() . DIRECTORY_SEPARATOR . str_replace(
                ' ',
                '_',
                $gp['mapping']['file']['name']
            ) . '.xml';
        ParserFactory::write(
            $path,
            ['mappings' => $mappings]
        );

        return $responseService->redirect();
    }

    /**
     * Returns the name of the mapping file from the GET param or null if the param is missing.
     *
     * @param \Fci\View\Service\RequestService $requestService
     *
     * @return string|null
     */
    protected function _getMappingFile(RequestService $requestService)
    {
        $gp = $requestService->getGetPost();

        if (array_key_exists('view', $gp) && array_key_exists('mapping', $gp['view'])) {
            return $gp['view']['mapping'];
        }

        return null;
    }
}
