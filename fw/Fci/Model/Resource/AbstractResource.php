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
 * @package     Fci_Model
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
abstract class Fci_Model_Resource_AbstractResource
{
    /**
     * @var int
     */
    protected static $_processedEntities = 0;

    /**
     * @var int
     */
    protected static $_insertedEntities = 0;

    /**
     * @var int
     */
    protected static $_updatedEntities = 0;

    /**
     * @var string
     */
    protected $_entityTable;

    /**
     * @var \Cemes\Pdo\Mysql
     */
    protected $_mysql;

    /**
     * @var Fci_Objects_Cache
     */
    protected $_cache;

    /**
     * @var Fci_Helper_Config
     */
    protected $_config;

    /**
     * @var Fci_Log_LoggerInterface
     */
    protected $_logger;

    /**
     * @var array[]
     */
    protected $_tableDescription = [];

    /**
     * Fci_Model_Resource_AbstractResource constructor.
     */
    public function __construct()
    {
        $this->_mysql = Cemes_Registry::get('db');
        $this->_cache = Fci_Objects_Cache::getInstance();
        $this->_config = Fci_Helper_Config::getInstance();
        $this->_logger = Cemes_Registry::get('logger');
    }

    /**
     * Inserts the given entity into the database.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    abstract public function insert(Fci_Model_AbstractEntity $entity);

    /**
     * Updates the given entity in the database.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    abstract public function update(Fci_Model_AbstractEntity $entity);

    /**
     * Modifies the entity before it is saved.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _beforeSave(Fci_Model_AbstractEntity $entity)
    {
        return $this;
    }

    /**
     * Modifies the entity after it is saved.
     *
     * @param Fci_Model_AbstractEntity $entity
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _afterSave(Fci_Model_AbstractEntity $entity)
    {
        return $this;
    }

    /**
     * Returns the table to save the entity in. If $suffix is given it will be added to the end of the string with an
     * underscore.
     *
     * @param string $suffix [optional] Table suffix.
     *
     * @return string
     */
    public function getEntityTable($suffix = '')
    {
        $entityTable = $this->_entityTable;

        if ($suffix) {
            $entityTable .= '_' . $suffix;
        }

        return $entityTable;
    }

    /**
     * @return int
     */
    public static function getProcessedEntities()
    {
        return static::$_processedEntities;
    }

    /**
     * @return int
     */
    public static function getInsertedEntities()
    {
        return static::$_insertedEntities;
    }

    /**
     * @return int
     */
    public static function getUpdatedEntities()
    {
        return static::$_updatedEntities;
    }

    /**
     * Returns the description of the given table as an array.
     * This output is cached.
     *
     * @param string $table
     *
     * @return array
     */
    protected function _getTableDescription($table)
    {
        if (!array_key_exists($table, $this->_tableDescription)) {
            $this->_tableDescription[$table] = $this->_mysql->describeTable($table);
        }

        return $this->_tableDescription[$table];
    }

    /**
     * Returns if the given field exists in the given table.
     *
     * @param string $table
     * @param string $field
     *
     * @return bool
     */
    protected function _fieldExistsInTable($table, $field)
    {
        $fields = $this->_getTableDescription($table);

        return array_key_exists($field, $fields);
    }

    /**
     * Returns if a table with the given name exists.
     *
     * @param string $table
     *
     * @return bool
     */
    protected function _tableExists($table)
    {
        try {
            $this->_mysql->query('SHOW TABLES LIKE \'' . $table . '\';');
        } catch (Exception $e) {
            return 0;
        }

        return $this->_mysql->count() !== 0;
    }
}
