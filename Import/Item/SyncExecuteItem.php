<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Import\Item;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * Import event item carrying both Doctrine entity and ES document.
 */
class SyncExecuteItem extends AbstractImportItem
{
    /**
     * @var array Sync storage data.
     */
    protected $syncStorageData;

    /**
     * @param mixed             $entity
     * @param DocumentInterface $document
     * @param array             $syncStorageData
     */
    public function __construct($entity, DocumentInterface $document, $syncStorageData)
    {
        parent::__construct($entity, $document);
        $this->syncStorageData = $syncStorageData;
    }

    /**
     * @return array
     */
    public function getSyncStorageData()
    {
        return $this->syncStorageData;
    }

    /**
     * @param array $syncStorageData
     */
    public function setSyncStorageData($syncStorageData)
    {
        $this->syncStorageData = $syncStorageData;
    }
}
