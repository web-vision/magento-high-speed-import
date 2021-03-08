<?php

namespace Fci\View;

use Cemes\Pdo\Mysql;

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
class Bootstrap
{
    /**
     * This method initializes constants, logger, database connection and the cache and then starts the routing process.
     */
    public function run()
    {
        $this->_defineResourceConstants();

        $this->_initLogger();

        try {
            $this->_initDatabase();
        } catch (\Exception $e) {
            \Cemes_Registry::get('logger')->critical('Could not connect to database.');
        }

        \Fci_Objects_Cache::getInstance(true);

        // a simple Routing for check is a Page call or Image/File call
        /** @var \Fci\View\Routing $routing */
        $routing = new Routing();
        $routing->start();
    }

    /**
     * Defines multiple constants used in the application.
     *
     * @return $this
     */
    protected function _defineResourceConstants()
    {
        define('Path_thisScript', $_SERVER['SCRIPT_NAME']);
        define('PATH_VIEW', ROOT_BASEDIR . 'view');
        define('Resource_path', PATH_VIEW . DIRECTORY_SEPARATOR . 'Resources');
        define('PrivateRes_path', PATH_VIEW . DIRECTORY_SEPARATOR . 'Private');
        define('PublicRes_path', PATH_VIEW . DIRECTORY_SEPARATOR . 'Public');
        define('Cache_path', PATH_VIEW . DIRECTORY_SEPARATOR . 'Cache');

        return $this;
    }

    /**
     * Initializes the connection to the database.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _initDatabase()
    {
        // load MySQL config from Magento
        $mysqlConf = \Fci_Helper_Factory_MagentoFactory::getMagento()->getDatabaseConfig();
        $mysql = new Mysql();
        $mysql->connect($mysqlConf);
        \Cemes_Registry::register('db', $mysql);
    }

    /**
     * Initializes the logger.
     */
    protected function _initLogger()
    {
        if (\Cemes::isCli()) {
            $logger = new \Fci_Log_CliLogger();
        } else {
            $logger = new \Fci_Log_HtmlLogger();
        }

        \Cemes_Registry::register('logger', $logger);
    }
}
