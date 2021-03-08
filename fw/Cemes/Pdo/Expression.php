<?php

namespace Cemes\Pdo;

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
 * @package     WebVision_mhsi
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Expression
{
    /**
     * Storage for the SQL expression.
     *
     * @var string
     */
    protected $_expression;

    /**
     * Constructor which initializes the expression.
     *
     * @param string $expression The string containing a SQL expression.
     */
    public function __construct($expression)
    {
        $this->_expression = (string)$expression;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_expression;
    }
}
