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
class Mysql extends AbstractConnection
{
    const REPLACE = 'REPLACE INTO';

    /**
     * Mysql constructor.
     */
    public function __construct()
    {
        $this->_pdoType = 'mysql';
    }

    /**
     * @inheritDoc
     */
    protected function _getDsn($config)
    {
        $dsn = parent::_getDsn($config);

        if (array_key_exists('charset', $config)) {
            $dsn .= ';charset=' . $config['charset'];
        }

        return $dsn;
    }

    /**
     * Returns an replace statement.
     *
     * @param string|array $table The table to replace into or array with 'as' => 'identifier'.
     *
     * @return Statement
     * @throws DatabaseException
     */
    public function replace($table)
    {
        return new Statement($this, self::REPLACE, $table);
    }
}
