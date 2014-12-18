<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\EventListener;

use ONGR\ConnectionsBundle\Pipeline\Item\AbstractImportItem;

/**
 * ImportModifyEventListener class - assigns data from doctrine item to Elasticsearch document.
 */
class ImportModifyEventListener extends AbstractImportModifyEventListener
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
