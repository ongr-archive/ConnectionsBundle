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

/**
 * Interface for SyncStorage Storage Manager.
 */
interface StorageManagerInterface
{
    const STATUS_NEW = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_ERROR = 2;

    /**
     * Creates storage space.
     *
     * @param int   $shopId
     * @param mixed $connection
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function createStorage($shopId = null, $connection = null);

    /**
     * Adds record to storage or updates existing one with new date and time.
     *
     * @param string   $operationType
     * @param string   $documentType
     * @param int      $documentId
     * @param DateTime $dateTime
     * @param array    $shopIds
     */
    public function addRecord($operationType, $documentType, $documentId, DateTime $dateTime, array $shopIds = null);

    /**
     * Removes record from storage for selected shops.
     *
     * @param int   $syncStorageStorageRecordId
     * @param array $shopIds
     */
    public function removeRecord($syncStorageStorageRecordId, array $shopIds = null);

    /**
     * Returns next $count (or less) of records available for processing.
     *
     * @param int    $count
     * @param string $documentType
     * @param int    $shopId
     *
     * @return array
     */
    public function getNextRecords($count, $documentType = null, $shopId = null);
}
