<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffImport;

use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;

/**
 * Class for import sync data to ElasticSearch.
 */
abstract class DiffImport
{
    /**
     * @var PantherInterface
     */
    private $storage;

    /**
     * Imports sync data to ElasticSearch.
     */
    abstract public function import();

    /**
     * Set Panther storage facility.
     *
     * @param PantherInterface $storage
     */
    public function setStorageFacility(PantherInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Panther storage facility.
     *
     * @return PantherInterface
     */
    public function getStorageFacility()
    {
        return $this->storage;
    }
}
