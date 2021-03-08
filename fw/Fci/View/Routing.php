<?php

namespace Fci\View;

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
class Routing
{
    /**
     * Handles the routing based on the controller and action $_GET param.
     *
     * @throws \Exception
     */
    public function start()
    {
        /** @var RequestService $requestService */
        $requestService = new RequestService();
        $requestService->buildRequests(
            $_GET,
            $_POST,
            isset($_SESSION) ? $_SESSION : [],
            $_COOKIE,
            $_REQUEST,
            $_SERVER
        );

        /** @var ResponseService $responseService */
        $responseService = new ResponseService();

        $view = array_key_exists('view', $_GET) ? $_GET['view'] : [];
        $controllerName = 'home';
        if (isset($view['controller'])) {
            $controllerName = filter_var($view['controller'], FILTER_SANITIZE_STRING);
        }

        $actionType = 'index';
        if (isset($view['action'])) {
            $actionType = filter_var($view['action'], FILTER_SANITIZE_STRING);
        }

        if (!empty($controllerName)) {
            $className = '\\Fci\\View\\Controller\\' . ucfirst($controllerName) . 'Controller';
            if (class_exists($className)) {
                $object = new $className();
                if (is_object($object)) {
                    if (!empty($actionType)) {
                        $action = $actionType . 'Action';
                        if (!method_exists($object, $action)) {
                            throw new \Exception('Invalid action called ' . 1465641752);
                        }
                        /** @var ResponseService $response */
                        $response = $object->$action($requestService, $responseService);
                        if (is_a($response, ResponseService::class)) {
                            if ($response->redirectFlag) {
                                exit;
                            }
                            echo $response->getContent();
                        } else {
                            header("HTTP/1.0 404 Not Found");
                            // Vielleicht 404 Controller?
                            echo '404 Not Found';
                            die();
                        }
                    } else {
                        header("HTTP/1.0 404 Not Found");
                        // Vielleicht 404 Controller?
                        echo '404 Not Found';
                        die();
                    }
                } else {
                    throw new \Exception('Object konnte nicht gefunden werden ' . 1465641635);
                }
            }
        }
    }
}
