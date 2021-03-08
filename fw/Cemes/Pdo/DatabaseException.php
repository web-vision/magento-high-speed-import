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
 * @package     Cemes\Pdo
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class DatabaseException extends \Exception
{
    /**
     * DatabaseException constructor.
     */
    public function __construct($message, $code = 0, \Exception $exception = null)
    {
        parent::__construct($message, $code, $exception);

        if ($exception && $exception instanceof \PDOException) {
            $this->code = $exception->getCode();
        }
    }
}
