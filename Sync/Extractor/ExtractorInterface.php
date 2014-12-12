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

use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\BaseDiffItem;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;

/**
 * Sync data extractor.
 */
interface ExtractorInterface
{
    /**
     * Extract data to full stack.
     *
     * @param BaseDiffItem $item
     *
     * @return void
     */
    public function extract(BaseDiffItem $item);

    /**
     * Set SyncStorage storage facility.
     *
     * @param SyncStorageInterface $storage
     *
     * @return void
     */
    public function setStorageFacility(SyncStorageInterface $storage);

    /**
     * SyncStorage storage facility.
     *
     * @return SyncStorageInterface
     */
    public function getStorageFacility();
}
