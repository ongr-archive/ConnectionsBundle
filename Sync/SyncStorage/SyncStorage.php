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
use InvalidArgumentException;
use ONGR\ConnectionsBundle\Sync\SyncStorage\StorageManager\StorageManagerInterface;

/**
 * Class for SyncStorage storage manipulation.
 */
class SyncStorage implements SyncStorageInterface
{
    /**
     * @var StorageManagerInterface
     */
    private $storageManager;

    /**
     * Dependency injection.
     *
     * @param StorageManagerInterface $storageManager
     */
    public function __construct(StorageManagerInterface $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save($operationType, $documentType, $documentId, DateTime $dateTime, array $shopIds = null)
    {
        if (!$this->isValidOperation($operationType) || empty($documentType) || $documentId === 0) {
            throw new InvalidArgumentException('Invalid parameters specified.');
        }

        $this->storageManager->addRecord($operationType, $documentType, $documentId, $dateTime, $shopIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getChunk($size = 1, $documentType = null, $shopId = null)
    {
        if ($size === 0) {
            return null;
        }

        return $this->storageManager->getNextRecords($size, $documentType, $shopId);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($itemId, array $shopIds = null)
    {
        if ($itemId === 0) {
            return;
        }

        $this->storageManager->removeRecord($itemId, $shopIds);
    }

    /**
     * Validates operation type.
     *
     * @param string $operationType
     *
     * @return bool
     */
    private function isValidOperation($operationType)
    {
        $operationType = strtolower($operationType);

        return in_array(
            $operationType,
            [
                self::OPERATION_CREATE,
                self::OPERATION_UPDATE,
                self::OPERATION_DELETE,
            ]
        );
    }
}
