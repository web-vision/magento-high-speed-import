<?php

namespace Fci\View\Controller;

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
class HomeController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function indexAction(RequestService $requestService, ResponseService $responseService)
    {
        $configService = new ConfigService('bin');

        $data = $this->_view->getDataArray();
        $data['files'] = $configService->getFiles();

        $mappings = $configServices = new ConfigService('bin/mappings');
        $data['mappings'] = $mappings->getFiles();

        $data['mappingClone'] = [];
        $data['mappingClone'][] = ['value' => '0', 'label' => 'Select...', 'selected' => false];
        foreach ($data['mappings'] as $mapping) {
            $data['mappingClone'][] = ['value' => $mapping['name'], 'label' => $mapping['name'], 'selected' => false];
        }

        return $responseService->setContent(
            $this->_view->render('Pages/Home.twig', $data)
        );
    }
}
