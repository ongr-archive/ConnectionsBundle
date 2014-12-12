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

/**
 * ImportModifyEvent class - assigns data from doctrine item to Elasticsearch document.
 */
class ImportModifyEvent extends AbstractImportModifyEventListener
{
    /**
     * Modifies EventItem.
     *
     * @param AbstractImportItem $eventItem
     */
    protected function modify(AbstractImportItem $eventItem)
    {
        $this->assignDataToDocument($eventItem->getDocument(), $eventItem->getEntity());
    }
}
