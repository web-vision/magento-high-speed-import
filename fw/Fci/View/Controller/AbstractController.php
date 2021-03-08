<?php

namespace Fci\View\Controller;

use Fci\View\Service\RequestService;
use Fci\View\Service\ResponseService;
use Fci\View\View;

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
abstract class AbstractController
{
    /**
     * @var \Fci\View\View $_view
     */
    protected $_view;

    /**
     * AbstractController constructor.
     */
    public function __construct()
    {
        $this->_view = new View();
    }

    /**
     * The initial action of the controller.
     *
     * @param \Fci\View\Service\RequestService  $requestService
     * @param \Fci\View\Service\ResponseService $responseService
     *
     * @return \Fci\View\Service\ResponseService
     */
    abstract public function indexAction(RequestService $requestService, ResponseService $responseService);
}
