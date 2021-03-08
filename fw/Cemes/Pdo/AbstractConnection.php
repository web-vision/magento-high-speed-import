<?php

namespace Cemes\Pdo;

use \PDO;
use \PDOStatement;

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
abstract class AbstractConnection
{
    const FETCH_ASSOC = 'assoc';
    const FETCH_NUM   = 'num';
    const FETCH_BOTH  = 'both';
    const FETCH_OBJ   = 'object';

    /**
     * Has to be set by implementing class.
     *
     * @var string
     */
    protected $_pdoType = '';

    /**
     * Specifies the case of column names retrieved in queries.
     *
     * @var integer
     */
    protected $_caseFolding = PDO::CASE_NATURAL;

    /**
     * The connection to the database.
     *
     * @var PDO
     */
    protected $_connection;

    /**
     * The result of the last statement.
     *
     * @var PDOStatement
     */
    protected $_result;

    /**
     * The insert id of the last statement.
     *
     * @var int
     */
    protected $_insertId = -1;

    /**
     * The fetch mode.
     *
     * @var int
     */
    protected $_fetchMode = PDO::FETCH_ASSOC;

    /**
     * The identifier symbol which should be escaped.
     *
     * @var string
     */
    protected $_quoteIdentifierSymbol = '`';

    /**
     * Destructor to make sure to close the connection.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Establishes the connection to a database.
     *
     * @param array $config
     *
     * @throws DatabaseException
     */
    public function connect($config)
    {
        if ($this->isConnected()) {
            throw new DatabaseException('Connection already initialized.', E_NOTICE);
        }

        if (!extension_loaded('pdo')) {
            throw new DatabaseException('The PDO extension is required but it is not loaded.');
        }

        if (!in_array($this->_pdoType, PDO::getAvailableDrivers(), true)) {
            throw new DatabaseException('The ' . $this->_pdoType . ' driver is not currently installed');
        }

        try {
            $this->_connection = new PDO(
                $this->_getDsn($config), $config['username'], $config['password'], $this->_getOptions($config)
            );

            $this->_connection->setAttribute(PDO::ATTR_CASE, $this->_caseFolding);

            // always use exceptions
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Closes the connection to the database.
     */
    public function close()
    {
        $this->_connection = null;
    }

    /**
     * Returns if a connection to the database has been established.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->_connection instanceof PDO;
    }

    /**
     * Returns if the connection is currently in a transaction or not.
     *
     * @return bool
     */
    public function inTransaction()
    {
        if ($this->isConnected()) {
            return $this->_connection->inTransaction();
        }

        return false;
    }

    /**
     * Starts a transaction.
     */
    public function beginTransaction()
    {
        if ($this->isConnected() && !$this->inTransaction()) {
            $this->_connection->beginTransaction();
        }
    }

    /**
     * Commits a transaction.
     */
    public function commit()
    {
        if ($this->isConnected() && $this->inTransaction()) {
            $this->_connection->commit();
        }
    }

    /**
     * Rolls back a transaction.
     */
    public function rollBack()
    {
        if ($this->isConnected() && $this->inTransaction()) {
            $this->_connection->rollBack();
        }
    }

    /**
     * Returns an insert statement.
     *
     * @param string|array $table The table to insert into or array with 'as' => 'identifier'.
     *
     * @return Statement
     * @throws DatabaseException
     */
    public function insert($table)
    {
        return new Statement($this, Statement::INSERT, $table);
    }

    /**
     * Returns a select statement.
     *
     * @param string|array $table   The table to select from or array with 'as' => 'identifier'.
     * @param string|array $columns The column(s) to select from the table.
     *
     * @return Statement
     * @throws DatabaseException
     */
    public function select($table, $columns = Statement::SQL_WILDCARD)
    {
        return new Statement($this, Statement::SELECT, $table, $columns);
    }

    /**
     * Returns an update statement.
     *
     * @param string|array $table The table to update or array with 'as' => 'identifier'.
     *
     * @return Statement
     * @throws DatabaseException
     */
    public function update($table)
    {
        return new Statement($this, Statement::UPDATE, $table);
    }

    /**
     * Returns a delete statement.
     *
     * @param string|array $table The table to delete from or array with 'as' => 'identifier'.
     *
     * @return Statement
     * @throws DatabaseException
     */
    public function delete($table)
    {
        return new Statement($this, Statement::DELETE, $table);
    }

    /**
     * Prepares a sql query via the connection.
     *
     * @param string $query
     *
     * @return \PDOStatement
     * @throws DatabaseException
     */
    public function prepare($query)
    {
        if ($this->isConnected()) {
            return $this->_connection->prepare($query);
        }

        throw new DatabaseException('No connection to a database.');
    }

    /**
     * Executes a query.
     *
     * @param string|Statement $queryStr
     * @param array|null       $bind
     *
     * @return PDOStatement
     * @throws DatabaseException
     */
    public function query($queryStr, $bind = null)
    {
        if (!$this->isConnected()) {
            throw new DatabaseException('No connection to a database.');
        }

        if ($queryStr instanceof Statement) {
            if (!$bind) {
                $bind = $queryStr->getBind();
            }
            $queryStr = $queryStr->assemble();
        }

        if (!is_array($bind)) {
            $bind = [$bind];
        }

        foreach ($bind as $name => $value) {
            if (!is_int($name) && strpos($name, ':') !== 0) {
                $newName = ':' . $name;
                unset($bind[$name]);
                $bind[$newName] = $value;
            }
        }

        $this->_result = $this->_connection->prepare($queryStr);
        $this->_result->execute($bind);
        $this->_result->setFetchMode($this->_fetchMode);

        if (stripos($queryStr, 'INSERT') === 0) {
            $this->_insertId = $this->_connection->lastInsertId();
        } else {
            $this->_insertId = -1;
        }

        if ((int)$this->_result->errorCode()) {
            $message = $this->_result->errorInfo();
            throw new DatabaseException($message[2] . PHP_EOL . $queryStr);
        }

        return $this->_result;
    }

    /**
     * Executes a query and returns the number of affected rows.
     *
     * @param string|Statement $queryStr
     *
     * @return int
     * @throws DatabaseException
     */
    public function exec($queryStr)
    {
        if (!$this->isConnected()) {
            throw new DatabaseException('No connection to a database.');
        }

        if ($queryStr instanceof Statement) {
            $queryStr = $queryStr->assemble();
        }

        try {
            $this->_result = null;

            $affectedRows = $this->_connection->exec($queryStr);

            if ($affectedRows === false) {
                $message = $this->_result->errorInfo();
                throw new DatabaseException($message[2] . PHP_EOL . $queryStr);
            }

            return $affectedRows;
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage() . PHP_EOL . $queryStr, 0, $e);
        }
    }

    /**
     * Returns the number of rows in a result set or the number of affected rows.
     *
     * @return int
     */
    public function count()
    {
        if ($this->_result instanceof PDOStatement && $this->isConnected()) {
            return $this->_result->rowCount();
        }

        return 0;
    }

    /**
     * If last statement was an insert statement this function will return the last inserted id.
     * Otherwise it will return -1.
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->_insertId;
    }

    /**
     * Sets the default fetch mode.
     *
     * @param string|int $fetchMode
     *
     * @throws DatabaseException
     */
    public function setFetchMode($fetchMode)
    {
        // WVTODO there are more fetch modes try to support as many as possible
        switch ($fetchMode) {
            case PDO::FETCH_LAZY:
            case PDO::FETCH_ASSOC:
            case PDO::FETCH_NUM:
            case PDO::FETCH_BOTH:
            case PDO::FETCH_NAMED:
            case PDO::FETCH_OBJ:
                $this->_fetchMode = $fetchMode;
                break;
            case self::FETCH_ASSOC:
                $this->_fetchMode = PDO::FETCH_ASSOC;
                break;
            case self::FETCH_NUM:
                $this->_fetchMode = PDO::FETCH_NUM;
                break;
            case self::FETCH_BOTH:
                $this->_fetchMode = PDO::FETCH_BOTH;
                break;
            case self::FETCH_OBJ:
                $this->_fetchMode = PDO::FETCH_OBJ;
                break;
            default:
                throw new DatabaseException('Invalid or unsupported fetch mode "' . $fetchMode . '" specified');
                break;
        }
    }

    /**
     * Converts a given fetch mode into the PDO fetch mode.
     *
     * @param string|int $type
     *
     * @return int
     */
    protected function _getPdoFetchMode($type)
    {
        if (!$type) {
            return $this->_fetchMode;
        }

        switch (strtolower($type)) {
            case PDO::FETCH_BOTH:
            case self::FETCH_BOTH:
                return PDO::FETCH_BOTH;
            case PDO::FETCH_NUM:
            case self::FETCH_NUM:
                return PDO::FETCH_NUM;
            case PDO::FETCH_OBJ:
            case self::FETCH_OBJ:
                return PDO::FETCH_OBJ;
            case PDO::FETCH_LAZY:
                return PDO::FETCH_LAZY;
            case PDO::FETCH_NAMED:
                return PDO::FETCH_NAMED;
            case PDO::FETCH_ASSOC:
            case self::FETCH_ASSOC:
                return PDO::FETCH_ASSOC;
            default:
                return $this->_fetchMode;
        }
    }

    /**
     * Executes the given query and returns the first entry in the defined fetch mode.
     * If field is specified only this field will be returned.
     * If there is no entry this function will return false.
     *
     * @param string|Statement|null $query
     * @param string|null           $field
     * @param int|string|null       $fetchMode
     *
     * @return array|bool|mixed
     * @throws DatabaseException
     */
    public function fetchEntry($query = null, $field = null, $fetchMode = null)
    {
        if (null !== $query) {
            $this->query($query);
        }

        return $this->fetchNextEntry($field, $fetchMode);
    }

    /**
     * Returns the next entry of the result set in the defined fetch mode.
     * If field is specified only this field will be returned.
     * If there is no entry this function will return false.
     *
     * @param string|null     $field
     * @param int|string|null $fetchMode
     *
     * @return array|bool|mixed
     * @throws DatabaseException
     */
    public function fetchNextEntry($field = null, $fetchMode = null)
    {
        if (!$this->_result instanceof PDOStatement) {
            return false;
        }

        if (!is_string($field) || empty($field)) {
            $field = null;
        }

        $fetchMode = $this->_getPdoFetchMode($fetchMode);

        if ($field === null) {
            return $this->_result->fetch($fetchMode);
        }

        if (!($entry = $this->_result->fetch($fetchMode))) {
            return false;
        }
        if ($fetchMode === PDO::FETCH_OBJ && property_exists($entry, $field)) {
            return $entry->$field;
        }
        if (array_key_exists($field, $entry)) {
            return $entry[$field];
        }

        throw new DatabaseException('Field "' . $field . '" doesn\'t exists in current result set.', E_WARNING);
    }

    /**
     * Executes the given query and returns the all entries in the defined fetch mode.
     * If field is specified only this field will be returned.
     * If there is no entry this function will return false.
     *
     * @param string|Statement $query
     * @param null|string      $field
     * @param int|string|null  $fetchMode
     *
     * @return array|bool
     * @throws DatabaseException
     */
    public function fetchAll($query = null, $field = null, $fetchMode = null)
    {
        if (null !== $query) {
            $this->query($query);
        }

        if (!is_string($field)) {
            $field = null;
        }

        $fetchMode = $this->_getPdoFetchMode($fetchMode);

        if ($field === null) {
            return $this->_result->fetchAll($fetchMode);
        }

        $output = [];
        if (!($entries = $this->_result->fetchAll($fetchMode))) {
            return false;
        }
        foreach ($entries as $entry) {
            if ($fetchMode === PDO::FETCH_OBJ && property_exists($entry, $field)) {
                $output[] = $entry->$field;
            } elseif (array_key_exists($field, $entry)) {
                $output[] = $entry[$field];
            } else {
                throw new DatabaseException('Field "' . $field . '" doesn\'t exists in current result set.', E_WARNING);
                break;
            }
        }

        return $output;
    }

    /**
     * Quotes the given value to prevent SQL Injections.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function quote($value)
    {
        if ($value instanceof Statement) {
            return '(' . $value->assemble() . ')';
        }

        if ($value instanceof Expression) {
            return $value->__toString();
        }

        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val);
            }

            return implode(', ', $value);
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return $this->_connection->quote($value);
    }

    /**
     * Quotes the value and replaces all '?' in the given string with the value.
     *
     * @param string $string
     * @param string $value
     *
     * @return string
     */
    public function quoteInto($string, $value)
    {
        return str_replace('?', $this->quote($value), $string);
    }

    /**
     * Handles the identifier based on its type. An {@see Expression} will be used as is. A {@see Statement} will be
     * assembled and surrounded by brakes. A string will be exploded at a '.' and then be handled as an array. Each
     * value of an array will be quoted and then concatenated by a '.'.
     * If an alias is given it will be quoted as well and added to the identifier with the word 'AS'.
     *
     * @param mixed       $identifier
     * @param null|string $alias
     *
     * @return string
     */
    public function quoteIdentifier($identifier, $alias = null)
    {
        if ($identifier instanceof Expression) {
            $quoted = $identifier->__toString();
        } elseif ($identifier instanceof Statement) {
            $quoted = '(' . $identifier->assemble() . ')';
        } else {
            if (is_string($identifier)) {
                $identifier = explode('.', $identifier);
            }
            if (is_array($identifier)) {
                $segments = [];
                foreach ($identifier as $segment) {
                    if ($segment instanceof Expression) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->_quoteIdentifier($segment);
                    }
                }
                if ($alias !== null && end($identifier) === $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->_quoteIdentifier($identifier);
            }
        }

        if ($alias !== null) {
            $quoted .= ' AS ' . $this->_quoteIdentifier($alias);
        }

        return $quoted;
    }

    /**
     * Quotes the identifier.
     *
     * @param string $identifier
     *
     * @return string
     */
    protected function _quoteIdentifier($identifier)
    {
        $q = $this->_quoteIdentifierSymbol;

        return $q . str_replace($q, $q . $q, trim($identifier, $q)) . $q;
    }

    /**
     * This method will build the dns string to connect to the database.
     *
     * @param array $config
     *
     * @return string
     * @throws DatabaseException
     */
    protected function _getDsn($config)
    {
        // don't pass following data into the dsn
        unset(
            $config['type'],
            $config['username'],
            $config['password'],
            $config['options'],
            $config['charset'],
            $config['persistent'],
            $config['driver_options']
        );

        $dsn = [];
        foreach ($config as $key => $value) {
            $dsn[] = $key . '=' . $value;
        }

        return $this->_pdoType . ':' . implode(';', $dsn);
    }

    /**
     * This method will return the options for initializing the connection.
     *
     * @param array $config
     *
     * @return array
     */
    protected function _getOptions($config)
    {
        $options = array_key_exists('driver_options', $config) ? $config['driver_options'] : [];
        if (array_key_exists('init_statement', $config)) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = $config['init_statement'];
        }
        if (array_key_exists('persistent', $config) && ($config['persistent'] == true)) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }

        return $options;
    }

    /**
     * Sets the case folding. Has to be set before the connection is established.
     *
     * @param $caseFolding
     *
     * @throws DatabaseException
     */
    public function setCaseFolding($caseFolding)
    {
        switch ($caseFolding) {
            case PDO::CASE_NATURAL:
            case PDO::CASE_UPPER:
            case PDO::CASE_LOWER:
                $this->_caseFolding = (int)$caseFolding;
                break;
            default:
                throw new DatabaseException('Case folding "' . $caseFolding . '" is not supported.', E_WARNING);
        }
    }

    /**
     * Returns an array which describes the table.
     *
     * @param $tableName
     *
     * @return array
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function describeTable($tableName)
    {
        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $result = $this->fetchAll('DESCRIBE ' . $this->quoteIdentifier($tableName), null, PDO::FETCH_NUM);

        $field = 0;
        $type = 1;
        $null = 2;
        $key = 3;
        $default = 4;
        $extra = 5;

        $desc = [];
        $i = 1;
        $p = 1;
        foreach ($result as $row) {
            $length = $scale = $precision = $unsigned = $primaryPosition = null;
            $primary = $identity = false;
            if (false !== strpos($row[$type], 'unsigned')) {
                $unsigned = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = (int)$matches[2];
            } elseif (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'decimal';
                $precision = (int)$matches[1];
                $scale = (int)$matches[2];
            } elseif (preg_match('/^float\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'float';
                $precision = (int)$matches[1];
                $scale = (int)$matches[2];
            } elseif (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                // The optional argument of a MySQL int type is not precision
                // or length; it is only a hint for display width.
            }
            if (strtoupper($row[$key]) === 'PRI') {
                $primary = true;
                $primaryPosition = $p;
                if ($row[$extra] === 'auto_increment') {
                    $identity = true;
                }
                ++$p;
            }
            $desc[$row[$field]] = [
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $row[$field],
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row[$type],
                'DEFAULT'          => $row[$default],
                'NULLABLE'         => $row[$null] === 'YES',
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => $unsigned,
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity,
            ];
            ++$i;
        }

        return $desc;
    }
}
