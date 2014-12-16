<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest;

use ONGR\ConnectionsBundle\Event\AbstractInitialSyncModifyEvent;
use ONGR\ConnectionsBundle\Event\ImportItem;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product;

/**
 * Implementation of InitialSyncModifyEventListener.
 */
class TestModifyEventListener extends AbstractInitialSyncModifyEvent
{
    /**
     * Assigns data in entity to relevant fields in document.
     *
     * @param ImportItem $eventItem
     */
    protected function modify(ImportItem $eventItem)
    {
        /** @var TestProduct $data */
        $data = $eventItem->getEntity();
        /** @var Product $document */
        $document = $eventItem->getDocument();
        $document->setId($data->id);
        $document->setTitle($data->title);
        $document->setPrice($data->price);
        $document->setDescription($data->description);
    }
}
