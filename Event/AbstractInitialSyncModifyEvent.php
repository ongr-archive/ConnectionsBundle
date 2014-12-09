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

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Abstract InitialSyncModifyEvent.
 */
abstract class AbstractInitialSyncModifyEvent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Modifies EventItem.
     *
     * @param ImportItem $eventItem
     */
    abstract protected function modify(ImportItem $eventItem);

    /**
     * Modify event.
     *
     * @param ItemPipelineEvent $event
     */
    public function onModify(ItemPipelineEvent $event)
    {
        if ($event instanceof ItemPipelineEvent) {
            $item = $event->getItem();
            if ($item instanceof ImportItem) {
                $this->modify($item);
            } else {
                if ($this->logger) {
                    $this->logger->notice('Item provided is not an ImportItem');
                }
            }
        } else {
            if ($this->logger) {
                $this->logger->notice('Event provided is not an ItemPipelineEvent');
            }
        }
    }
}
