<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\StorageManager;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager;

/**
 * The service to create/update database table and manipulate its data for SyncStorage.
 */
class MysqlStorageManager extends TableManager implements StorageManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStorage($shopId = null, $connection = null)
    {
        $connection = $connection ? : $this->getConnection();
        $schemaManager = $connection->getSchemaManager();
        $tableName = $this->getTableName($shopId);

        if ($schemaManager->tablesExist([$tableName])) {
            return true;
        }

        $table = new Table($tableName);
        $this->buildTable($table);
        $schemaManager->createTable($table);

        return true;
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

        $table->addColumn('timestamp', 'datetime');

        $table->addColumn('status', 'boolean', ['default' => self::STATUS_NEW])
            ->setComment('0-new,1-inProgress,2-error');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['type', 'document_type', 'document_id', 'status']);
    }

    /**
     * Returns table name for specified shop.
     *
     * @param int $shopId
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function getTableName($shopId = null)
    {
        $tableName = parent::getTableName();
        if (!preg_match('|^[a-z_0-9]+$|i', $tableName)) {
            throw new InvalidArgumentException("Invalid table name specified: \"$tableName\"");
        }

        if ($shopId !== null) {
            $tableName .= '_' . $shopId;
        }

        return $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function addRecord($operationType, $documentType, $documentId, DateTime $dateTime, array $shopIds = null)
    {
        if (empty($shopIds)) {
            $shopIds = [null];
        }

        $connection = $this->getConnection();

        foreach ($shopIds as $shopId) {
            $tableName = $connection->quoteIdentifier($this->getTableName($shopId));

            try {
                $sql = sprintf(
                    "INSERT INTO {$tableName}
                        (`type`, `document_type`, `document_id`, `timestamp`, `status`)
                    VALUES
                        (:operationType, :documentType, :documentId, :timestamp, :status)"
                );
                $statement = $connection->prepare($sql);
                $statement->execute(
                    [
                        'operationType' => $operationType,
                        'documentType' => $documentType,
                        'documentId' => $documentId,
                        'timestamp' => $dateTime->format('Y-m-d H:i:s'),
                        'status' => self::STATUS_NEW,
                    ]
                );
            } catch (DBALException $e) {
                // Record exists, check if update is needed.
                $sql = sprintf(
                    "SELECT COUNT(*) AS count FROM {$tableName}
                    WHERE
                        `type` = :operationType
                        AND `document_type` = :documentType
                        AND `document_id` = :documentId
                        AND `status` = :status
                        AND `timestamp` >= :dateTime"
                );
                $statement = $connection->prepare($sql);
                $statement->execute(
                    [
                        'operationType' => $operationType,
                        'documentType' => $documentType,
                        'documentId' => $documentId,
                        'status' => self::STATUS_NEW,
                        'dateTime' => $dateTime->format('Y-m-d H:i:s'),
                    ]
                );
                $newerRecordExists = $statement->fetchColumn(0) > 0;
                if ($newerRecordExists) {
                    continue;
                }

                // More recent record info, attempt to update existing record.
                $sql = sprintf(
                    "UPDATE {$tableName}
                    SET `timestamp` = :dateTime
                    WHERE
                        `type` = :operationType
                        AND `document_type` = :documentType
                        AND `document_id` = :documentId
                        AND `status` = :status"
                );
                $statement = $connection->prepare($sql);
                $statement->execute(
                    [
                        'dateTime' => $dateTime->format('Y-m-d H:i:s'),
                        'operationType' => $operationType,
                        'documentType' => $documentType,
                        'documentId' => $documentId,
                        'status' => self::STATUS_NEW,
                    ]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRecord($syncStorageStorageRecordId, array $shopIds = null)
    {
        if (empty($shopIds)) {
            $shopIds = [null];
        }

        $connection = $this->getConnection();

        foreach ($shopIds as $shopId) {
            try {
                $connection->delete($this->getTableName($shopId), ['id' => $syncStorageStorageRecordId]);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNextRecords($count, $documentType = null, $shopId = null)
    {
        $count = (int)$count;
        if ($count === 0) {
            return [];
        }

        $connection = $this->getConnection();
        $connection->beginTransaction();

        $tableName = $connection->quoteIdentifier($this->getTableName($shopId));

        $baseParams = [
            ['limit', $count, \PDO::PARAM_INT],
        ];

        $documentTypeCondition = '';
        if (!empty($documentType) && is_string($documentType)) {
            $documentTypeCondition = ' AND `document_type` = :documentType';
            $baseParams[] = ['documentType', $documentType, \PDO::PARAM_STR];
        }

        // Select records for update.
        $sqlSelectForUpdate = sprintf(
            "SELECT *, :shopId AS `shop_id` FROM {$tableName}
            WHERE
                `status` = :status %s
            ORDER BY `timestamp` ASC, `id` ASC
            LIMIT :limit
            FOR UPDATE",
            $documentTypeCondition
        );

        $params = [
            ['shopId', $shopId, \PDO::PARAM_INT],
            ['status', self::STATUS_NEW, \PDO::PARAM_INT],
        ];

        $statement = $connection->prepare($sqlSelectForUpdate);
        $this->bindParams($statement, array_merge_recursive($params, $baseParams));
        $statement->execute();
        $nextRecords = $statement->fetchAll();

        // Update status.
        $sqlUpdate = sprintf(
            "UPDATE {$tableName}
            SET `status` = :toStatus
            WHERE
                `status` = :fromStatus %s
            ORDER BY `timestamp` ASC, `id` ASC
            LIMIT :limit",
            $documentTypeCondition
        );

        $params = [
            ['fromStatus', self::STATUS_NEW, \PDO::PARAM_INT],
            ['toStatus', self::STATUS_IN_PROGRESS, \PDO::PARAM_INT],
        ];

        $statement = $connection->prepare($sqlUpdate);
        $this->bindParams($statement, array_merge_recursive($params, $baseParams));
        $statement->execute();
        $connection->commit();

        return $nextRecords;
    }

    /**
     * Bind params to SQL statement.
     *
     * @param Statement $statement
     * @param array     $params
     */
    private function bindParams($statement, $params)
    {
        foreach ($params as $param) {
            $statement->bindValue($param[0], $param[1], $param[2]);
        }
    }
}
