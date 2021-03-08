<?php

namespace Fci\View;

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
class View extends Render
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * View constructor.
     */
    public function __construct()
    {
        parent::__construct(
            [
                'environment'         => [
                    'debug'            => true,
                    'cache'            => true,
                    'auto_reload'      => false,
                    'strict_variables' => true,
                    'autoescape'       => true,
                ],
                'defaultTemplatePath' => 'Private/Templates',
            ]
        );
    }

    /**
     * @return array
     */
    public function getDataArray()
    {
        return $this->data;
    }
}
