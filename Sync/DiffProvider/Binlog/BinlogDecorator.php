<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog;

use Doctrine\DBAL\Connection;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DiffItemFactory;

/**
 * Decorator for binary logs which changes column indexes to names.
 */
class BinlogDecorator implements \Iterator
{
    /**
     * @var string Base name of the bin logs.
     */
    private $baseName;

    /**
     * @var string Directory of the bin logs.
     */
    private $directory;

    /**
     * @var array Mapping from ordinal field position to associative for all tables.
     */
    private $mappings = [];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string Connection to be used.
     */
    private $connectionName = 'default';

    /**
     * @var BinlogParser
     */
    private $binlogParser;

    /**
     * @param Connection $connection
     * @param string     $dir
     * @param string     $baseName
     * @param \DateTime  $from
     * @param string     $connectionName
     */
    public function __construct(Connection $connection, $dir, $baseName, \DateTime $from, $connectionName = 'default')
    {
        $this->connection = $connection;
        $this->connectionName = $connectionName;
        $this->directory = $dir;
        $this->baseName = $baseName;
        $this->binlogParser = new BinlogParser($this->directory, $this->baseName, $from);
    }

    /**
     * Returns table mapping from ordinal field position to associative for all tables.
     *
     * @param string $table
     *
     * @return array|bool
     * @throws \UnderflowException
     */
    protected function getTableMapping($table)
    {
        if (array_key_exists($table, $this->mappings)) {
            return $this->mappings[$table];
        }

        $mapping = $this->retrieveMapping($table);

        if (empty($mapping)) {
            throw new \UnderflowException("Table with name {$table} not found.");
        }

        $this->mappings[$table] = $mapping;

        return $mapping;
    }

    /**
     * Retrieves mapping from database.
     *
     * @param string $table
     *
     * @return array|bool
     */
    protected function retrieveMapping($table)
    {
        $result = $this->connection->fetchAll(
            'SELECT
               COLUMN_NAME,
               ORDINAL_POSITION
             FROM
               INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_NAME = ?',
            [$table]
        );

        if (empty($result)) {
            return false;
        }

        $columns = [];

        foreach ($result as $column) {
            $columns[$column['ORDINAL_POSITION']] = $column['COLUMN_NAME'];
        }

        return $columns;
    }

    /**
     * Applies associative mapping to numbered columns.
     *
     * @param array $params
     * @param array $mapping
     *
     * @return array
     */
    public function applyMapping($params, $mapping)
    {
        $newParams = [];

        foreach ($params as $key => $value) {
            $newParams[$mapping[$key]] = $value;
        }

        return $newParams;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $buffer = $this->binlogParser->current();
        $type = $buffer[BinlogParser::PARAM_QUERY]['type'];

        $diffItem = DiffItemFactory::create($type);
        $diffItem->setTimestamp($buffer[BinlogParser::PARAM_DATE]);
        $diffItem->setCategory($buffer[BinlogParser::PARAM_QUERY]['table']);
        $mapping = $this->getTableMapping($diffItem->getCategory());

        if (isset($buffer[BinlogParser::PARAM_QUERY]['where'])) {
            $where = $buffer[BinlogParser::PARAM_QUERY]['where'];
            $diffItem->setWhereParams($this->applyMapping($where, $mapping));
        }

        if (isset($buffer[BinlogParser::PARAM_QUERY]['set'])) {
            $set = $buffer[BinlogParser::PARAM_QUERY]['set'];
            $diffItem->setSetParams($this->applyMapping($set, $mapping));
        }

        return $diffItem;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->binlogParser->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->binlogParser->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->binlogParser->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->binlogParser->rewind();
    }
}
