<?php

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
 * @package     Fci_Helper
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Helper_Magento_Magento1 extends Fci_Helper_Magento_AbstractMagento
{
    /**
     * @var array
     */
    protected static $_tables = [
        'websites'     => 'core_website',
        'stores'       => 'core_store',
        'store_groups' => 'core_store_group',
        'index'        => 'index_process',
    ];

    /**
     * @inheritDoc
     */
    public function getDatabaseConfig()
    {
        /** @var Cemes_Helper_Config $mysqlConf */
        $mysqlConf = Cemes::loadModule('Helper/Config')->setPath(ROOT_BASEDIR . '../app/etc/');
        // if mysqlConf is false the config couldn't be read
        if ($mysqlConf->load('local.xml') === false) {
            Cemes_Registry::get('logger')->critical('Can\'t read MySQL configuration.');
        }

        $mysql = [
            'host'     => $mysqlConf->get('global/resources/default_setup/connection/host/@cdata'),
            'username' => $mysqlConf->get('global/resources/default_setup/connection/username/@cdata'),
            'password' => $mysqlConf->get('global/resources/default_setup/connection/password/@cdata'),
            'dbname'   => $mysqlConf->get('global/resources/default_setup/connection/dbname/@cdata'),
        ];

        return $mysql;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        include_once dirname(ROOT_BASEDIR) . CDS . 'app' . CDS . 'Mage.php';
        Mage::app('admin');
        // remove magento error handler
        restore_error_handler();
    }

    /**
     * @inheritDoc
     */
    public function setLocale($storeCode)
    {
        $locale = Mage::getStoreConfig('general/locale/code', $storeCode);
        Mage::getSingleton('core/translate')->setLocale($locale)->init('frontend');
    }

    /**
     * @inheritDoc
     */
    public function setCurrentStore($storeCode)
    {
        Mage::app()->setCurrentStore($storeCode);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeOptions($attributeSourceModel)
    {
        return Mage::getModel($attributeSourceModel)->getAllOptions();
    }

    /**
     * @inheritDoc
     */
    public function getTable($internalTableName)
    {
        return static::$_tables[$internalTableName];
    }

    /**
     * @inheritDoc
     */
    public function unload()
    {
        Mage::reset();
    }

    /**
     * @inheritDoc
     */
    public function postProcessEntity(Fci_Model_AbstractEntity $entity)
    {
        // unimplemented as there are no Magento 1 specific things to do
    }

    /**
     * @inheritDoc
     */
    public function getIndexState()
    {
        return 'require_reindex';
    }

    /**
     * @inheritDoc
     */
    public function getMediaDirectory()
    {
        return realpath(ROOT_BASEDIR . '../media');
    }
}
