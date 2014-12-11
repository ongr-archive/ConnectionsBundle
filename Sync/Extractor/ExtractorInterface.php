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
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;

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
     * Set Panther storage facility.
     *
     * @param PantherInterface $storage
     *
     * @return void
     */
    public function setStorageFacility(PantherInterface $storage);

    /**
     * Panther storage facility.
     *
     * @return PantherInterface
     */
    public function getStorageFacility();
}
