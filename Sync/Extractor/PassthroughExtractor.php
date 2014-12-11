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
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\BaseDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DeleteDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;

/**
 * Very simple data extractor for data synchronization.
 */
class PassthroughExtractor implements ExtractorInterface
{
    /**
     * @var PantherInterface
     */
    private $storage;

    /**
     * {@inheritdoc}
     */
    public function extract(BaseDiffItem $item)
    {
        $itemId = $item->getItemId();
        if (!is_numeric($itemId)) {
            throw new InvalidArgumentException('No valid item ID provided.');
        }

        if ($item instanceof CreateDiffItem) {
            $this->storage->save(
                PantherInterface::OPERATION_CREATE,
                $item->getCategory(),
                $itemId,
                $item->getTimestamp()
            );
        }
        if ($item instanceof UpdateDiffItem) {
            $this->storage->save(
                PantherInterface::OPERATION_UPDATE,
                $item->getCategory(),
                $itemId,
                $item->getTimestamp()
            );
        }
        if ($item instanceof DeleteDiffItem) {
            $this->storage->save(
                PantherInterface::OPERATION_DELETE,
                $item->getCategory(),
                $itemId,
                $item->getTimestamp()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStorageFacility(PantherInterface $storage)
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
}
