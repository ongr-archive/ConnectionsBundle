<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * SyncImportModifyEvent class - assigns data from doctrine item to Elasticsearch document.
 */
class SyncImportModifyEvent extends AbstractInitialSyncModifyEvent
{
    /**
     * Assigns raw data to given object.
     *
     * @param DocumentInterface $document
     * @param mixed             $data
     */
    protected function assignDataToDocument(DocumentInterface $document, $data)
    {
        foreach ($data as $property => $value) {
            if (property_exists(get_class($document), $property)) {
                $document->$property = $value;
            }
        }
    }

    /**
     * Modifies EventItem.
     *
     * @param AbstractImportItem $eventItem
     */
    protected function modify(AbstractImportItem $eventItem)
    {
        /** @var SyncImportItem $eventItem */
        if ($eventItem->getPantherData()['type'] == PantherInterface::OPERATION_CREATE) {
            $this->assignDataToDocument($eventItem->getDocument(), $eventItem->getEntity());
        } elseif ($eventItem->getPantherData()['type'] == PantherInterface::OPERATION_UPDATE) {
            $this->assignDataToDocument($eventItem->getDocument(), $eventItem->getEntity());
        }
    }
}
