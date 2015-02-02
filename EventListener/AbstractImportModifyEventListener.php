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
use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\ImportItem;
use ONGR\ConnectionsBundle\Pipeline\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
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

        if ($item instanceof ImportItem) {
            $this->modify($item, $event);
        } elseif ($item instanceof SyncExecuteItem) {
            $syncStorageData = $item->getSyncStorageData();

            if ($syncStorageData['type'] !== ActionTypes::DELETE) {
                $this->modify($item, $event);
            }
        } else {
            $this->log('The type of provided item is not ImportItem or SyncExecuteItem.', LogLevel::NOTICE);
        }
    }

    /**
     * Assigns raw data to given object.
     *
     * @param AbstractImportItem $eventItem
     * @param ItemPipelineEvent  $event
     */
    abstract protected function modify(AbstractImportItem $eventItem, ItemPipelineEvent $event);
}
