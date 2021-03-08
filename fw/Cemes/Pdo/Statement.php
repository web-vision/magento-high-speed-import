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
class Statement
{
    // partnames
    const TYPE         = 'type';
    const DISTINCT     = 'distinct';
    const COLUMNS      = 'columns';
    const UNION        = 'union';
    const FROM         = 'from';
    const VALUES       = 'values';
    const SET          = 'set';
    const WHERE        = 'where';
    const GROUP        = 'group';
    const HAVING       = 'having';
    const ORDER        = 'order';
    const LIMIT_COUNT  = 'limitcount';
    const LIMIT_OFFSET = 'limitoffset';
    // statement types
    const INSERT = 'INSERT INTO';
    const SELECT = 'SELECT';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';
    // join types
    const INNER_JOIN   = 'inner join';
    const LEFT_JOIN    = 'left join';
    const RIGHT_JOIN   = 'right join';
    const FULL_JOIN    = 'full join';
    const CROSS_JOIN   = 'cross join';
    const NATURAL_JOIN = 'natural join';
    // query string constants
    const SQL_WILDCARD   = '*';
    const SQL_UNION      = 'UNION';
    const SQL_UNION_ALL  = 'UNION ALL';
    const SQL_FROM       = 'FROM';
    const SQL_VALUES     = 'VALUES';
    const SQL_SET        = 'SET';
    const SQL_WHERE      = 'WHERE';
    const SQL_DISTINCT   = 'DISTINCT';
    const SQL_GROUP_BY   = 'GROUP BY';
    const SQL_ORDER_BY   = 'ORDER BY';
    const SQL_HAVING     = 'HAVING';
    const SQL_FOR_UPDATE = 'FOR UPDATE';
    const SQL_AND        = 'AND';
    const SQL_AS         = 'AS';
    const SQL_OR         = 'OR';
    const SQL_ON         = 'ON';
    const SQL_ASC        = 'ASC';
    const SQL_DESC       = 'DESC';
    const SQL_LIMIT      = 'LIMIT';
    const SQL_OFFSET     = 'OFFSET';

    /**
     * The initial set of parts of a query in the order they should be rendered.
     *
     * @var array
     */
    protected static $_partsInit = [
        self::TYPE         => null,
        self::DISTINCT     => false,
        self::COLUMNS      => [],
        self::UNION        => [],
        self::FROM         => [],
        self::VALUES       => [],
        self::SET          => [],
        self::WHERE        => [],
        self::GROUP        => [],
        self::HAVING       => [],
        self::ORDER        => [],
        self::LIMIT_COUNT  => null,
        self::LIMIT_OFFSET => null,
    ];

    /**
     * Specify legal join types.
     *
     * @var array
     */
    protected static $_joinTypes = [
        self::INNER_JOIN,
        self::LEFT_JOIN,
        self::RIGHT_JOIN,
        self::FULL_JOIN,
        self::CROSS_JOIN,
        self::NATURAL_JOIN,
    ];

    /**
     * Specify legal union types.
     *
     * @var array
     */
    protected static $_unionTypes = [
        self::SQL_UNION,
        self::SQL_UNION_ALL,
    ];

    /**
     * Specify legal statement types.
     *
     * @var array
     */
    protected static $_statementTypes = [
        self::SELECT,
        self::INSERT,
        self::UPDATE,
        self::DELETE,
        Mysql::REPLACE,
    ];

    /**
     * Specify predefined conditions.
     *
     * @var array
     */
    protected static $_conditionKeyMap = [
        'eq'      => '{{fieldName}} = ?',
        'neq'     => '{{fieldName}} != ?',
        'like'    => '{{fieldName}} LIKE ?',
        'nlike'   => '{{fieldName}} NOT LIKE ?',
        'in'      => '{{fieldName}} IN(?)',
        'nin'     => '{{fieldName}} NOT IN(?)',
        'is'      => '{{fieldName}} IS ?',
        'notnull' => '{{fieldName}} IS NOT NULL',
        'null'    => '{{fieldName}} IS NULL',
        'gt'      => '{{fieldName}} > ?',
        'lt'      => '{{fieldName}} < ?',
        'gteq'    => '{{fieldName}} >= ?',
        'lteq'    => '{{fieldName}} <= ?',
        'regexp'  => '{{fieldName}} REGEXP ?',
        'from'    => '{{fieldName}} >= ?',
        'to'      => '{{fieldName}} <= ?',
        'btw'     => '{{fieldName}} BETWEEN ? AND ?',
    ];

    /**
     * Specify control characters.
     *
     * @var array
     */
    protected static $_controlCharacters = [
        ';',
        '--',
        '#',
        '/*',
        '*/',
    ];

    /**
     * The actual parts of the current statement.
     *
     * @var array
     */
    protected $_parts = [];

    /**
     * The bind to use for the query.
     *
     * @var array
     */
    protected $_bind = [];

    /**
     * The connection to the database.
     *
     * @var \Cemes\Pdo\AbstractConnection
     */
    protected $_connection;

    /**
     * Statement constructor.
     *
     * @param \Cemes\Pdo\AbstractConnection     $connection The connection for quoting and fetching.
     * @param string                            $type       The statement type.
     * @param Expression|Statement|array|string $table      The initial table.
     * @param Expression|Statement|array|string $columns    [optional] The initial columns.
     *
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function __construct(AbstractConnection $connection, $type, $table, $columns = null)
    {
        $this->_connection = $connection;
        $this->_parts = static::$_partsInit;

        if (in_array($type, static::$_statementTypes, true)) {
            $this->_parts[static::TYPE] = $type;
        } else {
            throw new DatabaseException($type . ' is not a valid statement type');
        }

        if ($type === static::INSERT || $type === Mysql::REPLACE) {
            unset($this->_parts[static::COLUMNS]);
            \Cemes_StdLib::array_splice($this->_parts, static::VALUES, 0, [], static::COLUMNS);
        }

        $this->_join($table, null, $columns, static::FROM);
    }

    /**
     * Get bind variables.
     *
     * @return array
     */
    public function getBind()
    {
        return $this->_bind;
    }

    /**
     * Set bind variables.
     *
     * @param array $bind The array with values to bind to the statement.
     *
     * @return $this
     */
    public function bind($bind)
    {
        $this->_bind = $bind;

        return $this;
    }

    /**
     * Makes the query SELECT DISTINCT.
     *
     * @param bool $flag [optional] Whether or not the SELECT is DISTINCT (default true).
     *
     * @return $this
     */
    public function distinct($flag = true)
    {
        if ($this->_parts[static::TYPE] === static::SELECT) {
            $this->_parts[static::DISTINCT] = (bool)$flag;
        }

        return $this;
    }

    /**
     * Add a table and columns to the query.
     *
     * @param Expression|Statement|array|string $table   The table to get data from.
     * @param Expression|Statement|array|string $columns The columns to use from this table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function from($table, $columns = self::SQL_WILDCARD)
    {
        $this->_join($table, null, $columns, static::FROM);

        return $this;
    }

    /**
     * Adds one or more select queries as a union.
     *
     * @param Statement|array|string $select The select queries.
     * @param string                 $type   The union type.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function union($select, $type = self::SQL_UNION)
    {
        if (!is_array($select)) {
            $select = [$select];
        }

        if (!in_array($type, static::$_unionTypes, true)) {
            throw new DatabaseException('Invalid union type "' . $type . '"');
        }

        foreach ($select as $target) {
            if ($target instanceof Statement && $target->getType() === static::SELECT) {
                $this->_parts[static::UNION][] = [$target, $type];
            } elseif (is_string($target) && stripos($target, 'SELECT') === 0) {
                $this->_parts[static::UNION][] = [$target, $type];
            }
        }

        return $this;
    }

    /**
     * Creates an inner join to the given table.
     *
     * @param Expression|Statement|array|string $table     The table name.
     * @param string                            $condition The condition to join on.
     * @param Expression|Statement|array|string $columns   The columns to select from the joined table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function innerJoin($table, $condition, $columns = self::SQL_WILDCARD)
    {
        return $this->_join($table, $condition, $columns);
    }

    /**
     * Creates a left join to the given table.
     *
     * @param Expression|Statement|array|string $table     The table name.
     * @param string                            $condition The condition to join on.
     * @param Expression|Statement|array|string $columns   The columns to select from the joined table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function leftJoin($table, $condition, $columns = self::SQL_WILDCARD)
    {
        return $this->_join($table, $condition, $columns, static::LEFT_JOIN);
    }

    /**
     * Creates a right join to the given table.
     *
     * @param Expression|Statement|array|string $table     The table name.
     * @param string                            $condition The condition to join on.
     * @param Expression|Statement|array|string $columns   The columns to select from the joined table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function rightJoin($table, $condition, $columns = self::SQL_WILDCARD)
    {
        return $this->_join($table, $condition, $columns, static::RIGHT_JOIN);
    }

    /**
     * Creates a full join to the given table.
     *
     * @param Expression|Statement|array|string $table     The table name.
     * @param string                            $condition The condition to join on.
     * @param Expression|Statement|array|string $columns   The columns to select from the joined table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function fullJoin($table, $condition, $columns = self::SQL_WILDCARD)
    {
        return $this->_join($table, $condition, $columns, static::FULL_JOIN);
    }

    /**
     * Creates a cross join to the given table.
     *
     * @param Expression|Statement|array|string $table     The table name.
     * @param string                            $condition The condition to join on.
     * @param Expression|Statement|array|string $columns   The columns to select from the joined table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function crossJoin($table, $condition, $columns = self::SQL_WILDCARD)
    {
        return $this->_join($table, $condition, $columns, static::CROSS_JOIN);
    }

    /**
     * Creates a natural join to the given table.
     *
     * @param Expression|Statement|array|string $table     The table name.
     * @param string                            $condition The condition to join on.
     * @param Expression|Statement|array|string $columns   The columns to select from the joined table.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function naturalJoin($table, $condition, $columns = self::SQL_WILDCARD)
    {
        return $this->_join($table, $condition, $columns, static::NATURAL_JOIN);
    }

    /**
     * Adds a JOIN table and columns to the query.
     *
     * The $table and $columns parameters follow the same logic as described in the from() method.
     *
     * @param  Expression|Statement|array|string $table     The table name.
     * @param  string                            $condition The condition to join on.
     * @param  Expression|Statement|array|string $columns   The columns to select from the joined table.
     * @param  string                            $type      Type of join.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    protected function _join($table, $condition, $columns, $type = self::INNER_JOIN)
    {
        if ($type !== static::FROM && !in_array($type, static::$_joinTypes, true)) {
            throw new DatabaseException('Invalid join type \'' . $type . '\'');
        }

        if (count($this->_parts[static::UNION])) {
            throw new DatabaseException('Invalid use of table with ' . static::SQL_UNION);
        }

        if (!$table) {
            $alias = $tableName = '';
        } elseif (is_array($table)) {
            $_tableName = reset($table);
            $_alias = key($table);
            if (is_string($_alias)) {
                // We assume the key is the correlation name and value is the table name
                $tableName = $_tableName;
                $alias = $_alias;
            } else {
                // We assume just an array of identifiers, with no correlation name
                $tableName = $_tableName;
                $alias = $this->_uniqueCorrelation($tableName);
            }
        } elseif ($table instanceof Expression || $table instanceof Statement) {
            $tableName = $table;
            $alias = $this->_uniqueCorrelation('t');
        } elseif (preg_match('/^(.+)\s+AS\s+(.+)$/i', $table, $m)) {
            list(, $tableName, $alias) = $m;
        } else {
            $tableName = $table;
            $alias = $this->_uniqueCorrelation($tableName);
        }

        $lastFromAlias = null;
        if (!empty($alias)) {
            if (array_key_exists($alias, $this->_parts[static::FROM])) {
                throw new DatabaseException(
                    'You cannot define a correlation name \'' . $alias . '\' more than once'
                );
            }

            if ($type === static::FROM) {
                // append this from after the last from joinType
                $tmpFromParts = $this->_parts[static::FROM];
                $this->_parts[static::FROM] = [];
                // move all the froms onto the stack
                while ($tmpFromParts) {
                    $currentAlias = key($tmpFromParts);
                    if ($tmpFromParts[$currentAlias]['joinType'] !== static::FROM) {
                        break;
                    }
                    $lastFromAlias = $currentAlias;
                    $this->_parts[static::FROM][$currentAlias] = array_shift($tmpFromParts);
                }
            } else {
                $tmpFromParts = [];
            }
            $this->_parts[static::FROM][$alias] = [
                'joinType'      => $type,
                'tableName'     => $tableName,
                'joinCondition' => $condition,
            ];
            while ($tmpFromParts) {
                $currentAlias = key($tmpFromParts);
                $this->_parts[static::FROM][$currentAlias] = array_shift($tmpFromParts);
            }
        }

        // add to the columns from this joined table
        if ($type === static::FROM && $lastFromAlias === null) {
            $lastFromAlias = true;
        }
        $this->_tableCols($alias, $columns, $lastFromAlias);

        return $this;
    }

    /**
     * Adds a where condition. If another condition is present this one will be added with an AND.
     *
     * @param Statement|Expression|array|string $column    The column to check.
     * @param mixed                             $value     The value to check for.
     * @param string                            $condition The condition to use for comparison.
     * @param string                            $group     The group the condition is in.
     *
     * @return $this
     */
    public function where($column, $value, $condition = 'eq', $group = '0')
    {
        $this->_where($column, $value, $condition, $group, static::SQL_AND);

        return $this;
    }

    /**
     * Adds a where condition. If another condition is present this one will be added with an OR.
     *
     * @param Expression|Statement|array|string $column    The column to check.
     * @param mixed                             $value     The value to check for.
     * @param string                            $condition The condition to use for comparison.
     * @param string                            $group     The group the condition is in.
     *
     * @return $this
     */
    public function orWhere($column, $value, $condition = 'eq', $group = '0')
    {
        $this->_where($column, $value, $condition, $group, static::SQL_OR);

        return $this;
    }

    /**
     * Adds a where condition. If another condition is present this one will be added with the $conditionGlue.
     *
     * @param Expression|Statement|array|string $column        The column to check.
     * @param mixed                             $value         The value to check for.
     * @param string                            $conditionKey  The condition to use for comparison.
     * @param string                            $group         The group the condition is in.
     * @param string                            $conditionGlue The glue to use with the previous condition.
     *
     * @return $this
     */
    protected function _where($column, $value, $conditionKey, $group, $conditionGlue)
    {
        if (!array_key_exists($group, $this->_parts[static::WHERE])) {
            $this->_parts[static::WHERE][$group] = ['glue' => $conditionGlue];
        }

        if (!array_key_exists($conditionKey, static::$_conditionKeyMap)) {
            $conditionKey = 'eq';
        }

        if ($value instanceof Expression) {
            $condition = $value;
        } else {
            $condition = static::$_conditionKeyMap[$conditionKey];
            $condition = $this->_connection->quoteInto($condition, $value);
            $condition = str_replace('{{fieldName}}', $this->_connection->quoteIdentifier($column), $condition);
        }

        $where = [];
        $where['glue'] = $conditionGlue;
        $where['condition'] = $condition;
        $this->_parts[static::WHERE][$group][] = $where;

        return $this;
    }

    /**
     * Adds grouping to the query.
     *
     * @param Expression|Statement|array|string $columns The column(s) to group by.
     *
     * @return $this This Zend_Db_Select object.
     */
    public function groupBy($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $val) {
            if (preg_match('/\(.*\)/', (string)$val) && !$this->isContainControlCharacters((string)$val)) {
                $val = new Expression($val);
            }
            $this->_parts[static::GROUP][] = $val;
        }

        return $this;
    }

    /**
     * Adds a having condition. If another condition is present this one will be added with an AND.
     *
     * @param Expression|Statement|array|string $column    The column to check.
     * @param mixed                             $value     The value to check for.
     * @param string                            $condition The condition to use for comparison.
     * @param string                            $group     The group the condition is in.
     *
     * @return $this
     */
    public function having($column, $value, $condition = 'eq', $group = '0')
    {
        $this->_having($column, $value, $condition, $group, static::SQL_AND);

        return $this;
    }

    /**
     * Adds a having condition. If another condition is present this one will be added with an OR.
     *
     * @param Expression|Statement|array|string $column    The column to check.
     * @param mixed                             $value     The value to check for.
     * @param string                            $condition The condition to use for comparison.
     * @param string                            $group     The group the condition is in.
     *
     * @return $this
     */
    public function orHaving($column, $value, $condition = 'eq', $group = '0')
    {
        $this->_having($column, $value, $condition, $group, static::SQL_OR);

        return $this;
    }

    /**
     * Adds a having condition. If another condition is present this one will be added with a the $conditionGlue.
     *
     * @param Expression|Statement|string|array $column        The column to check.
     * @param mixed                             $value         The value to check for.
     * @param string                            $conditionKey  The condition to use for comparison.
     * @param string                            $group         The group the condition is in.
     * @param string                            $conditionGlue The glue to use with the previous condition.
     *
     * @return $this
     */
    protected function _having($column, $value, $conditionKey, $group, $conditionGlue)
    {
        if (!array_key_exists($group, $this->_parts[static::HAVING])) {
            $this->_parts[static::HAVING][$group] = ['glue' => $conditionGlue];
        }

        if (!array_key_exists($conditionKey, static::$_conditionKeyMap)) {
            $conditionKey = 'eq';
        }

        $condition = static::$_conditionKeyMap[$conditionKey];
        $condition = $this->_connection->quoteInto($condition, $value);
        $condition = str_replace('{{fieldName}}', $this->_connection->quoteIdentifier($column), $condition);

        $where = [];
        $where['glue'] = $conditionGlue;
        $where['condition'] = $condition;
        $this->_parts[static::HAVING][$group][] = $where;

        return $this;
    }

    /**
     * Orders the result by the given column(s).
     *
     * @param string|array $orders The column(s) to order by.
     *
     * @return $this
     */
    public function orderBy($orders)
    {
        if (!is_array($orders)) {
            $orders = [$orders];
        }

        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($orders as $order) {
            if ($order instanceof Expression) {
                $expr = $order->__toString();
                if (empty($expr)) {
                    continue;
                }
                $this->_parts[static::ORDER][] = $order;
            } else {
                if (empty($order)) {
                    continue;
                }

                $direction = static::SQL_ASC;

                if (preg_match('/(.*\W)(' . static::SQL_ASC . '|' . static::SQL_DESC . ')\b/si', $order, $matches)) {
                    $order = trim($matches[1]);
                    $direction = $matches[2];
                }

                if (preg_match('/^[\w]*\(.*\)$/', $order) && !$this->isContainControlCharacters($order)) {
                    $order = new Expression($order);
                }
                $this->_parts[static::ORDER][] = [$order, $direction];
            }
        }

        return $this;
    }

    /**
     * Sets a limit count and offset to the query.
     *
     * @param int $count  The number of rows to return.
     * @param int $offset OPTIONAL Start returning after this many rows.
     *
     * @return $this This object.
     */
    public function limit($count, $offset = null)
    {
        if ($count && $count > 0) {
            $this->_parts[static::LIMIT_COUNT] = (int)$count;
        }
        if ($offset && $offset > 0) {
            $this->_parts[static::LIMIT_OFFSET] = (int)$offset;
        }

        return $this;
    }

    /**
     * Add columns to the condition. If no table is given the initial table will be used.
     *
     * @param Expression|Statement|array|string $columns The column(s) to add.
     * @param Expression|Statement|array|string $table   [optional] The table the columns are in.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function columns($columns = self::SQL_WILDCARD, $table = null)
    {
        if ($table === null && count($this->_parts[static::FROM])) {
            reset($this->_parts[static::FROM]);
            $table = key($this->_parts[static::FROM]);
        }

        if (!array_key_exists($table, $this->_parts[static::FROM])) {
            throw new DatabaseException('No table has been specified for the FROM clause');
        }

        $this->_tableCols($table, $columns);

        return $this;
    }

    /**
     * Add values for an INSERT statement.
     *
     * @param mixed $values The values to input.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function values($values)
    {
        if ($this->getType() !== static::UPDATE
            && $this->getType() !== static::INSERT
            && $this->getType() !== Mysql::REPLACE) {
            return $this;
        }

        if ($values instanceof Statement && $values->getType() === static::SELECT) {
            if (count($this->_parts[static::VALUES])) {
                throw new DatabaseException(
                    'You can only use a select query as an value if no other values are present.'
                );
            }

            if (count($this->_parts[static::COLUMNS]) !== count($values->getPart(static::COLUMNS))) {
                throw new DatabaseException(
                    'The amount of columns of the select query has to be equal to the amount of columns in the insert.'
                );
            }

            $this->_parts[static::VALUES][] = $values;

            return $this;
        }

        if (!is_array($values)) {
            $values = [$values];
        } else {
            $values = array_values($values);
        }

        if (is_array($values[0])) {
            foreach ($values as $valueSet) {
                $this->values($valueSet);
            }

            return $this;
        }

        if (isset($this->_parts[static::VALUES][0]) && $this->_parts[static::VALUES][0] instanceof Statement) {
            throw new DatabaseException('You can\'t add values beside a select.');
        }

        if (count($values) !== count($this->_parts[static::COLUMNS])) {
            throw new DatabaseException('The amount if values has to be equal to the amount of columns.');
        }

        $this->_parts[static::VALUES][] = $values;

        return $this;
    }

    /**
     * Adding a column and a value for the SET call of an INSERT statement.
     *
     * @param Expression|Statement|array|string $column The column to set the value on.
     * @param mixed                             $value  The value to set.
     *
     * @return $this
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function set($column, $value)
    {
        if (count($this->_parts[static::COLUMNS])) {
            throw new DatabaseException('You can\'t use set and columns together.');
        }

        $this->_parts[static::SET][$column] = $value;

        return $this;
    }

    /**
     * Returns if the statement is a select, insert, update or delete query.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_parts[static::TYPE];
    }

    /**
     * Get part of the structured information for the current query.
     *
     * @param string $part The part to get.
     *
     * @return mixed
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function getPart($part)
    {
        $part = strtolower($part);
        if (!array_key_exists($part, $this->_parts)) {
            throw new DatabaseException('Invalid part "' . $part . '"');
        }

        return $this->_parts[$part];
    }

    /**
     * Resets the given part when it exists and $parts is a string.
     * Resets the given parts when they exists and $parts is an array.
     * Resets all parts when $parts is null.
     *
     * @param array|string|null $parts The part(s) to reset.
     *
     * @return $this
     */
    public function reset($parts = null)
    {
        if ($parts === null) {
            $this->_parts = static::$_partsInit;
        } elseif (is_array($parts)) {
            foreach ($parts as $part) {
                if (array_key_exists($part, $this->_parts)) {
                    $this->_parts[$part] = static::$_partsInit[$part];
                }
            }
        } elseif (is_string($parts) && array_key_exists($parts, $this->_parts)) {
            $this->_parts[$parts] = static::$_partsInit[$parts];
        }

        return $this;
    }

    /**
     * Returns the connection to the database.
     *
     * @return \Cemes\Pdo\AbstractConnection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Check is expression contains some MySql control characters.
     *
     * @param string $expression The expression to check.
     *
     * @return bool
     */
    public function isContainControlCharacters($expression)
    {
        foreach (static::$_controlCharacters as $controlChar) {
            if (strpos($expression, $controlChar) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executes the given query and returns the all entries in the defined fetch mode.
     * If field is specified only this field will be returned.
     * If there is no entry this function will return false.
     *
     * @param null|string     $field     [optional] A specific field to get from the result set.
     * @param int|string|null $fetchMode [optional] The fetch mode to use.
     *
     * @return array|bool
     * @throws DatabaseException
     */
    public function fetchAll($field = null, $fetchMode = null)
    {
        return $this->_connection->fetchAll($this, $field, $fetchMode);
    }

    /**
     * Executes the given query and returns the first entry in the defined fetch mode.
     * If field is specified only this field will be returned.
     * If there is no entry this function will return false.
     *
     * @param string|null     $field     [optional] A specific field to get from the result set.
     * @param int|string|null $fetchMode [optional] The fetch mode to use.
     *
     * @return array|bool|mixed
     * @throws DatabaseException
     */
    public function fetchEntry($field = null, $fetchMode = null)
    {
        return $this->_connection->fetchEntry($this, $field, $fetchMode);
    }

    /**
     * Returns the next entry of the result set in the defined fetch mode.
     * If field is specified only this field will be returned.
     * If there is no entry this function will return false.
     *
     * @param string|null     $field     [optional] A specific field to get from the result set.
     * @param int|string|null $fetchMode [optional] The fetch mode to use.
     *
     * @return array|bool|mixed
     * @throws DatabaseException
     */
    public function fetchNextEntry($field = null, $fetchMode = null)
    {
        return $this->_connection->fetchNextEntry($field, $fetchMode);
    }

    /**
     * Executes the statement and returns the amount of effected rows.
     *
     * @return int
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function execute()
    {
        try {
            return $this->_connection->exec($this);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage() . PHP_EOL . $this->assemble());
        }
    }

    /**
     * Executes the statement and returns the {@see PDOStatement}.
     *
     * @param array|null $bind
     *
     * @return \PDOStatement
     * @throws \Cemes\Pdo\DatabaseException
     */
    public function query($bind = null)
    {
        try {
            return $this->_connection->query($this, $bind);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage() . PHP_EOL . $this->assemble());
        }
    }

    /**
     * Assembles the statement.
     *
     * @return string
     */
    public function assemble()
    {
        $sql = $this->getType();
        foreach ($this->_parts as $part => $unused) {
            $method = '_render' . ucfirst($part);
            if (method_exists($this, $method)) {
                $sql = $this->$method($sql);
            }
        }

        return $sql;
    }

    /**
     * Render the distinct part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderDistinct($sql)
    {
        if ($this->_parts[static::DISTINCT] && $this->getType() === static::SELECT) {
            $sql .= ' ' . static::SQL_DISTINCT;
        }

        return $sql;
    }

    /**
     * Render the columns part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderColumns($sql)
    {
        if (!count($this->_parts[static::COLUMNS])) {
            return $sql;
        }

        $columns = [];
        foreach ($this->_parts[static::COLUMNS] as list($correlationName, $column, $alias)) {
            if ($column instanceof Expression) {
                $columns[] = $this->_connection->quoteIdentifier($column, $alias);
            } elseif ($column instanceof Statement) {
                $columns[] = $this->_connection->quoteIdentifier($column, $alias);
            } else {
                if ($column === static::SQL_WILDCARD) {
                    $column = new Expression(static::SQL_WILDCARD);
                    $alias = null;
                }
                if (empty($correlationName)) {
                    $columns[] = $this->_connection->quoteIdentifier($column, $alias);
                } else {
                    $columns[] = $this->_connection->quoteIdentifier([$correlationName, $column], $alias);
                }
            }
        }

        if ($this->getType() === static::INSERT || $this->getType() === Mysql::REPLACE) {
            $sql .= PHP_EOL . '(' . implode(', ', $columns) . ')';
        } else {
            $sql .= ' ' . implode(', ', $columns);
        }

        return $sql;
    }

    /**
     * Render the union part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderUnion($sql)
    {
        if ($this->_parts[static::UNION]) {
            $parts = count($this->_parts[static::UNION]);
            foreach ($this->_parts[static::UNION] as $cnt => list($target, $type)) {
                if ($target instanceof Statement) {
                    $target = $target->assemble();
                }
                $sql .= $target;
                if ($cnt < $parts - 1) {
                    $sql .= ' ' . $type . ' ';
                }
            }
        }

        return $sql;
    }

    /**
     * Render the from part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderFrom($sql)
    {
        $from = [];
        foreach ($this->_parts[static::FROM] as $correlationName => $table) {
            $tmp = '';

            $joinType = ($table['joinType'] === static::FROM) ? static::INNER_JOIN : $table['joinType'];

            // Add join clause (if applicable)
            if (!empty($from)) {
                $tmp .= strtoupper($joinType) . ' ';
            }

            $tmp .= $this->_connection->quoteIdentifier($table['tableName'], $correlationName);

            // Add join conditions (if applicable)
            if (!empty($from) && !empty($table['joinCondition'])) {
                $tmp .= ' ' . static::SQL_ON . ' ' . $table['joinCondition'];
            }

            $from[] = $tmp;
        }

        if (!empty($from)) {
            if ($this->getType() === static::SELECT || $this->getType() === static::DELETE) {
                $sql .= PHP_EOL . static::SQL_FROM;
            }
            $sql .= ' ' . implode(PHP_EOL, $from);
        }

        return $sql;
    }

    /**
     * Render the values part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderValues($sql)
    {
        if (!count($this->_parts[static::VALUES])) {
            return $sql;
        }

        if (count($this->_parts[static::VALUES]) === 1 && $this->_parts[static::VALUES][0] instanceof Statement) {
            return $sql . PHP_EOL . $this->_parts[static::VALUES][0]->assemble();
        }

        $values = [];
        foreach ($this->_parts[static::VALUES] as $valueSet) {
            $valueSet = array_map([$this->_connection, 'quote'], $valueSet);

            $values[] = '(' . implode(', ', $valueSet) . ')';
        }

        if ($values) {
            $sql .= PHP_EOL . static::SQL_VALUES . ' ' . implode(',' . PHP_EOL, $values);
        }

        return $sql;
    }

    /**
     * Render the set part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderSet($sql)
    {
        if (!count($this->_parts[static::SET])) {
            return $sql;
        }

        $sets = [];
        foreach ($this->_parts[static::SET] as $column => $value) {
            $sets[] = $this->_connection->quoteIdentifier($column) . ' = ' . $this->_connection->quote($value);
        }

        if ($sets) {
            $sql .= PHP_EOL . static::SQL_SET . ' ' . implode(', ', $sets);
        }

        return $sql;
    }

    /**
     * Render the where part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderWhere($sql)
    {
        if ($this->getType() === static::INSERT || $this->getType() === Mysql::REPLACE) {
            return $sql;
        }

        if (!empty($this->_parts[static::WHERE])) {
            $sql .= "\r\n" . static::SQL_WHERE . ' ';
            $where = '';

            foreach ($this->_parts[static::WHERE] as $group => $groupParts) {
                if (!empty($where)) {
                    $where .= ' ' . $groupParts['glue'] . ' ';
                }
                $where .= '(';
                for ($i = 0, $count = count($groupParts) - 1; $i < $count; $i++) {
                    if ($i > 0) {
                        $where .= ' ' . $groupParts[$i]['glue'] . ' ';
                    }
                    $where .= $groupParts[$i]['condition'];
                }
                $where .= ')';
            }

            $sql .= $where;
        }

        return $sql;
    }

    /**
     * Render the group part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderGroup($sql)
    {
        if ($this->_parts[static::FROM] && $this->_parts[static::GROUP]) {
            $group = [];
            foreach ($this->_parts[static::GROUP] as $term) {
                $group[] = $this->_connection->quoteIdentifier($term);
            }
            $sql .= PHP_EOL . static::SQL_GROUP_BY . ' ' . implode(',' . PHP_EOL, $group);
        }

        return $sql;
    }

    /**
     * Render the having part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderHaving($sql)
    {
        if ($this->getType() === static::INSERT || $this->getType() === Mysql::REPLACE) {
            return $sql;
        }

        if (!empty($this->_parts[static::HAVING])) {
            $sql .= "\r\n" . static::SQL_HAVING . ' ';
            $having = '';

            foreach ($this->_parts[static::HAVING] as $group => $groupParts) {
                if (!empty($having)) {
                    $having .= ' ' . $groupParts['glue'] . ' ';
                }
                $having .= '(';
                for ($i = 0, $count = count($groupParts) - 1; $i < $count; $i++) {
                    if ($i > 0) {
                        $having .= ' ' . $groupParts[$i]['glue'] . ' ';
                    }
                    $having .= $groupParts[$i]['condition'];
                }
                $having .= ')';
            }

            $sql .= $having;
        }

        return $sql;
    }

    /**
     * Render the order part.
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderOrder($sql)
    {
        if (!empty($this->_parts[static::ORDER])) {
            $sql .= PHP_EOL . static::SQL_ORDER_BY . ' ';

            $orders = [];
            foreach ($this->_parts[static::ORDER] as $order) {
                if (is_array($order)) {
                    $orders[] = $this->_connection->quoteIdentifier($order[0]) . ' ' . $order[1];
                } else {
                    $orders[] = $this->_connection->quoteIdentifier($order);
                }
            }

            $sql .= implode(', ', $orders);
        }

        return $sql;
    }

    /**
     * Render the limit part.
     *
     * @param string $sql
     *
     * @return string
     */
    public function _renderLimitcount($sql)
    {
        if ($this->_parts[static::LIMIT_OFFSET]) {
            $offset = (int)$this->_parts[static::LIMIT_OFFSET];
            $count = PHP_INT_MAX;
        }

        if ($this->_parts[static::LIMIT_COUNT]) {
            $count = (int)$this->_parts[static::LIMIT_COUNT];
        }

        if (isset($count)) {
            $sql .= ' ' . static::SQL_LIMIT . ' ' . $count;

            if (isset($offset)) {
                $sql .= ' ' . static::SQL_OFFSET . ' ' . $offset;
            }
        }

        return $sql;
    }

    /**
     * Add columns to the query.
     *
     * @param string|null  $table      The table the column is in.
     * @param string|array $columns    The column to add.
     * @param null|string  $afterTable [optional] The table to put the columns after.
     */
    protected function _tableCols($table, $columns, $afterTable = null)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($table === null) {
            $table = '';
        }

        $columnValues = [];

        foreach (array_filter($columns) as $columnAlias => $column) {
            $currentTable = $table;
            if (is_string($column)) {
                // Check for a column matching "<column> AS <alias>" and extract the alias name
                if (preg_match('/^(.+)\s+' . static::SQL_AS . '\s+(.+)$/i', $column, $m)) {
                    list(, $column, $columnAlias) = $m;
                }
                // Check for columns that look like functions and convert to Zend_Db_Expr
                if (preg_match('/\(.*\)/', (string)$column) && !$this->isContainControlCharacters($column)) {
                    $column = new Expression($column);
                } elseif (preg_match('/(.+)\.(.+)/', $column, $m)) {
                    list(, $currentTable, $column) = $m;
                }
            }
            $columnValues[] = [$currentTable, $column, is_string($columnAlias) ? $columnAlias : null];
        }

        if ($columnValues) {
            // should we attempt to prepend or insert these values?
            if ($afterTable === true || is_string($afterTable)) {
                $tmpColumns = $this->_parts[static::COLUMNS];
                $this->_parts[static::COLUMNS] = [];
            } else {
                $tmpColumns = [];
            }

            // find the correlation name to insert after
            if (is_string($afterTable)) {
                while ($tmpColumns) {
                    $this->_parts[static::COLUMNS][] = $currentColumn = array_shift($tmpColumns);
                    if ($currentColumn[0] === $afterTable) {
                        break;
                    }
                }
            }

            // apply current values to current stack
            foreach ($columnValues as $columnValue) {
                $this->_parts[static::COLUMNS][] = $columnValue;
            }

            // finish ensuring that all previous values are applied (if they exist)
            while ($tmpColumns) {
                $this->_parts[static::COLUMNS][] = array_shift($tmpColumns);
            }
        }
    }

    /**
     * Generate a unique correlation name.
     *
     * @param array|string $name A qualified identifier.
     *
     * @return string
     */
    private function _uniqueCorrelation($name)
    {
        if (is_array($name)) {
            $key = key($name);
            $correlation = is_string($key) ? $key : end($name);
        } else {
            // Extract just the last name of a qualified table name
            $dot = strrpos($name, '.');
            $correlation = ($dot === false) ? $name : substr($name, $dot + 1);
        }

        for ($i = 2; array_key_exists($correlation, $this->_parts[static::FROM]); $i++) {
            $correlation = $name . '_' . $i;
        }

        return $correlation;
    }

    /**
     * Assembles the query and returns it or an empty string if an error occurred.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $sql = $this->assemble();
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            $sql = '';
        }

        return $sql;
    }
}
