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

use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * ImportModifyEvent class - assigns data from doctrine item to Elasticsearch document.
 */
class ImportModifyEvent extends AbstractInitialSyncModifyEvent
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
     * @param ImportItem $eventItem
     */
    protected function modify(ImportItem $eventItem)
    {
        $this->assignDataToDocument($eventItem->getDocument(), $eventItem->getEntity());
    }
}
