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
 */
class ResponseService
{
    /**
     * @var array
     */
    protected $header;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var bool
     */
    public $redirectFlag = false;

    /**
     * Returns the content of the response.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the content of the response.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Redirects the user to the given $controller and $action. If $additional params are given these will be added.
     *
     * @param string $controller
     * @param string $action
     * @param array  $additional
     *
     * @return $this
     */
    public function redirect($controller = 'home', $action = 'index', $additional = [])
    {
        $this->redirectFlag = true;
        // Weiterleitungsziel inkl. 301 Status

        $domain = $_SERVER['HTTP_ORIGIN'] . $_SERVER['SCRIPT_NAME'];
        $params = '?view[controller]=' . $controller . '&view[action]=' . $action;
        if (!empty($additional)) {
            foreach ($additional as $paramKey => $value) {
                $params .= '&view[' . $paramKey . ']=' . $value;
            }
        }
        $url = $domain . $params;
        header('Location:' . $url, true, 301);

        return $this;
    }
}
