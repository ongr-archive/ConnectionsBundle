<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\Extractor;

use InvalidArgumentException;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\BaseDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DeleteDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;

/**
 * Very simple data extractor for data synchronization.
 */
class PassthroughExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * @var SyncStorageInterface
     */
    private $storage;

    /**
     * {@inheritdoc}
     */
    public function extract(BaseDiffItem $item)
    {
        if (!is_numeric($item->getItemId())) {
            throw new InvalidArgumentException('No valid item ID provided.');
        }

        if ($item instanceof CreateDiffItem) {
            $this->saveResult($item, ActionTypes::CREATE);
        }
        if ($item instanceof UpdateDiffItem) {
            $this->saveResult($item, ActionTypes::UPDATE);
        }
        if ($item instanceof DeleteDiffItem) {
            $this->saveResult($item, ActionTypes::DELETE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStorageFacility(SyncStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageFacility()
    {
        return $this->storage;
    }

    /**
     * Save results to storage.
     *
     * @param BaseDiffItem $item
     * @param string       $action
     */
    private function saveResult(BaseDiffItem $item, $action)
    {
        $this->storage->save(
            $action,
            $item->getCategory(),
            $item->getItemId(),
            $item->getTimestamp(),
            $this->getShopIds()
        );
    }
}
