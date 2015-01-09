<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;
use ONGR\ConnectionsBundle\Sync\SqlValidator;

/**
 * The service to create/update database table for synchronization jobs.
 */
class TableManager
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $shops;

    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param string     $tableName
     * @param array      $shops
     *
     * @throws \LogicException
     */
    public function __construct($connection, $tableName = 'ongr_sync_jobs', array $shops = [])
    {
        if ($connection === null) {
            throw new \LogicException('DBAL connection was not injected. Doctrine is missing?');
        }

        $this->connection = $connection;
        $this->setTableName($tableName);
        $this->shops = $shops;
    }

    /**
     * Creates table for sync jobs.
     *
     * @param Connection|null $connection
     *
     * @return bool|null
     */
    public function createTable($connection = null)
    {
        $connection = $connection ? : $this->connection;
        $schemaManager = $connection->getSchemaManager();

        if ($schemaManager->tablesExist([$this->tableName])) {
            return null;
        }

        $table = new Table($this->tableName);
        $this->buildTable($table);
        $schemaManager->createTable($table);

        return true;
    }

    /**
     * Updates table for sync jobs. Returns NULL if table is up-to-date.
     *
     * @param Connection|null $connection
     *
     * @return bool|null
     */
    public function updateTable($connection = null)
    {
        $connection = $connection ? : $this->connection;
        $schemaManager = $connection->getSchemaManager();

        if (!$schemaManager->tablesExist([$this->tableName])) {
            return false;
        }

        $table = new Table($this->tableName);
        $this->buildTable($table);
        $oldTable = $schemaManager->listTableDetails($this->tableName);

        $comparator = new Comparator();
        $diff = $comparator->diffTable($oldTable, $table);

        if (!$diff) {
            return null;
        }

        $schemaManager->alterTable($diff);

        return true;
    }

    /**
     * Returns connection.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns table name.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set table name.
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = SqlValidator::validateTableName($tableName);
    }

    /**
     * Builds table structure.
     *
     * @param Table $table
     */
    protected function buildTable(Table $table)
    {
        $table->addColumn('id', 'bigint')
            ->setUnsigned(true)
            ->setAutoincrement(true);

        $table->addColumn('type', 'string')
            ->setLength(1)
            ->setComment('C-CREATE(INSERT),U-UPDATE,D-DELETE');

        $table->addColumn('document_type', 'string')
            ->setLength(32);

        $table->addColumn('document_id', 'string')
            ->setLength(32);

        $table->addColumn('update_type', 'smallint')
            ->setDefault(1)
            ->setComment('0-partial,1-full');

        $table->addColumn('timestamp', 'datetime');

        $table->setPrimaryKey(['id']);

        $this->addStatusField($table);
    }

    /**
     * Dynamically add status field or fields in case of multi-shop.
     *
     * @param Table $table
     */
    protected function addStatusField(Table $table)
    {
        if (empty($this->shops)) {
            $table->addColumn('status', 'boolean', ['default' => 0])->setComment('0-new,1-done');
            $table->addIndex(['status']);
        } else {
            foreach ($this->shops as $shop) {
                $fieldName = "status_{$shop}";
                $table->addColumn($fieldName, 'boolean', ['default' => 0])->setComment('0-new,1-done');
                $table->addIndex([$fieldName]);
            }
        }
    }
}
