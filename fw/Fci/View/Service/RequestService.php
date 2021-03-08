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
class RequestService
{
    /**
     * @var array
     */
    protected $_getPost = [];

    /**
     * @var array
     */
    protected $_session;

    /**
     * @var array
     */
    protected $_cookie;

    /**
     * @var array
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_server;

    /**
     * RequestService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Fills the request object with the given data.
     *
     * @param array $get
     * @param array $post
     * @param array $session
     * @param array $cookie
     * @param array $request
     * @param array $server
     */
    public function buildRequests($get = [], $post = [], $session = [], $cookie = [], $request = [], $server = [])
    {
        if (!empty($get)) {
            $this->_getPost = $get;
        }
        if (!empty($post)) {
            $this->_getPost = array_merge_recursive($this->_getPost, $post);
        }
        if (!empty($session)) {
            $this->_session = $session;
        }
        if (!empty($cookie)) {
            $this->_cookie = $cookie;
        }
        if (!empty($request)) {
            $this->_request = $request;
        }
        if (!empty($server)) {
            $this->_server = $server;
        }
    }

    /**
     * Returns an array with the data from the $_GET and $_POST globals.
     *
     * @return array
     */
    public function getGetPost()
    {
        return $this->_getPost;
    }

    /**
     * Returns an array with the data from the $_SESSION global.
     *
     * @return array|null
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Sets the session variable.
     *
     * @param array|null $session
     */
    public function setSession($session)
    {
        $this->_session = $session;
    }

    /**
     * Returns an array with the data from the $_COOKIE global.
     *
     * @return array|null
     */
    public function getCookie()
    {
        return $this->_cookie;
    }

    /**
     * Returns an array with the data from the $_REQUEST global.
     *
     * @return array|null
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns an array with the data from the $_SERVER global.
     *
     * @return array|null
     */
    public function getServer()
    {
        return $this->_server;
    }
}
