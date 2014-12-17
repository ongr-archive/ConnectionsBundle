<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Fixtures\Import;

use ONGR\ConnectionsBundle\EventListener\AbstractImportModifyEventListener;
use ONGR\ConnectionsBundle\Import\Item\AbstractImportItem;

/**
 * Implementation of InitialSyncModifyEventListener.
 */
class TestModifyEventListener extends AbstractImportModifyEventListener
{
    /**
     * Does nothing.
     *
     * @param AbstractImportItem $eventItem
     */
    protected function modify(AbstractImportItem $eventItem)
    {
    }
}
