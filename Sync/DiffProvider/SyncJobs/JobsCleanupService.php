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
use InvalidArgumentException;
use ONGR\ConnectionsBundle\Entity\SyncJob;
use ONGR\ConnectionsBundle\Sync\SqlValidator;
use Psr\Log\LoggerAwareTrait;

/**
 * Service which cleans sync jobs table.
 */
class JobsCleanupService
{
    use LoggerAwareTrait;

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
     */
    public function __construct($connection, $tableName = 'ongr_sync_jobs', array $shops = [])
    {
        $this->connection = $connection;
        $this->tableName = SqlValidator::validateTableName($tableName);
        $this->shops = $shops;
    }

    /**
     * Does actual cleaning up.
     */
    public function doCleanup()
    {
        $result = $this->connection->executeQuery(
            $this->generateCleanupQuery()
        );

        if ($this->logger) {
            $this->logger->info('Number of rows deleted: ' . $result->rowCount());
        }
    }

    /**
     * Generates a query.
     *
     * @return string
     */
    protected function generateCleanupQuery()
    {
        $query = 'DELETE FROM ' . $this->tableName . ' WHERE';

        if (empty($this->shops)) {
            return $query . ' `status` = ' . SyncJob::STATUS_DONE;
        }

        $conditions = [];
        foreach ($this->shops as $shop) {
            $conditions[] = " `status_{$shop}` = " . SyncJob::STATUS_DONE;
        }

        return $query . implode(' AND', $conditions);
    }
}
