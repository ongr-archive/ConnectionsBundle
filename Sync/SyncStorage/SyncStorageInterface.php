<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\SyncStorage;

use DateTime;

/**
 * Temp data storage. (codename: SyncStorage).
 */
interface SyncStorageInterface
{
    const STORAGE_MYSQL = 'mysql';

    const OPERATION_CREATE = 'c';
    const OPERATION_UPDATE = 'u';
    const OPERATION_DELETE = 'd';

    /**
     * Save data to temp storage.
     *
     * @param string   $operationType
     * @param string   $documentType
     * @param int      $documentId
     * @param DateTime $dateTime
     * @param array    $shopIds
     */
    public function save($operationType, $documentType, $documentId, DateTime $dateTime, array $shopIds = null);

    /**
     * Get data chunk from temp storage.
     *
     * @param int    $size         Chunk size.
     * @param string $documentType Document type.
     * @param int    $shopId       Shop ID.
     *
     * @return array
     */
    public function getChunk($size = 1, $documentType = null, $shopId = null);

    /**
     * Delete item from temp storage.
     *
     * @param int   $itemId
     * @param array $shopIds
     */
    public function deleteItem($itemId, array $shopIds = null);
}
