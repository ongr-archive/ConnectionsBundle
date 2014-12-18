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

use ONGR\ConnectionsBundle\Import\Item\AbstractImportItem;
use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * AbstractImportModifyEventListener - assigns data from entity to document.
 */
abstract class AbstractImportModifyEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

    /**
     * Modify event.
     *
     * @param ItemPipelineEvent $event
     */
    public function onModify(ItemPipelineEvent $event)
    {
        $item = $event->getItem();
        if ($item instanceof AbstractImportItem) {
            $this->modify($item);
        } else {
            $this->log('Item provided is not an AbstractImportItem', LogLevel::NOTICE);
        }
    }

    /**
     * Assigns raw data to given object.
     *
     * @param AbstractImportItem $eventItem
     */
    abstract protected function modify(AbstractImportItem $eventItem);
}
