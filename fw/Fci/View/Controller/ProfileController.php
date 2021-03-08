<?php

namespace Fci\View\Controller;

use Cemes\Parser\ParserFactory;
use Cemes_StdLib;
use Fci\View\Service\CacheFormatService;
use Fci\View\Service\ConfigService;
use Fci\View\Service\FormFieldFormatService;
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
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class ProfileController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function indexAction(RequestService $requestService, ResponseService $responseService)
    {
        $configFile = $this->_getConfigFile($requestService);

        $configService = new ConfigService('bin');
        $default = $configService->getDefaultConfig($configFile);

        $fieldFormat = new FormFieldFormatService();
        $fieldFormat->formatField($default);

        $cacheFormatService = new CacheFormatService();

        $data = $this->_view->getDataArray();
        $data['config'] = $default;
        $data['configFile'] = $configFile;
        $data['scriptEvents'] = $fieldFormat->formatScriptEventSelect();
        $data['productAttributes'] = $cacheFormatService->getProductAttributes();
        $data['categoryAttributes'] = $cacheFormatService->getCategoryAttributes();

        return $responseService->setContent(
            $this->_view->render('Pages/Profile.twig', $data)
        );
    }

    /**
     * An action to save a profile.
     *
     * @param RequestService  $requestService
     * @param ResponseService $responseService
     *
     * @return ResponseService
     */
    public function saveAction(RequestService $requestService, ResponseService $responseService)
    {
        $gp = $requestService->getGetPost();
        $configFile = $this->_getConfigFile($requestService);

        if (isset($gp['view']['submit']) && !empty($gp['view']['submit'])) {
            $configService = new ConfigService('bin');
            $default = $configService->getDefaultConfig($configFile);

            if (array_key_exists('general', $default) && array_key_exists('scripts', $default['general'])) {
                $fieldFormat = new FormFieldFormatService();
                $default['general']['scripts'] = $fieldFormat->formatScriptFields($default);
            }

            $config = Cemes_StdLib::array_merge_recursive_distinct($default, $gp['config']);

            $config['general_defaults']['websites'] = implode(',', $config['general_defaults']['websites']);
            $config['general_defaults']['store'] = implode(',', $config['general_defaults']['store']);

            ParserFactory::write($configService->getFilePath($configFile), ['config' => $config]);
        }

        return $responseService->redirect(
            'profile',
            'index',
            [
                'configfile' => $configFile,
            ]
        );
    }

    /**
     * Action to delete a profile.
     *
     * @param \Fci\View\Service\RequestService  $requestService
     * @param \Fci\View\Service\ResponseService $responseService
     *
     * @return ResponseService
     */
    public function deleteAction(RequestService $requestService, ResponseService $responseService)
    {
        $configFile = $this->_getConfigFile($requestService);

        $configService = new ConfigService('bin');
        $configService->delete($configFile);

        return $responseService->redirect();
    }

    /**
     * Returns the name of the config file from the GET param or null if the param is missing.
     *
     * @param \Fci\View\Service\RequestService $requestService
     *
     * @return string|null
     */
    protected function _getConfigFile(RequestService $requestService)
    {
        $gp = $requestService->getGetPost();

        if (array_key_exists('view', $gp) && array_key_exists('configfile', $gp['view'])) {
            return $gp['view']['configfile'];
        }

        return null;
    }
}
