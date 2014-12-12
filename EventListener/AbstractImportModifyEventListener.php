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
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Abstract import modify event listener.
 */
abstract class AbstractImportModifyEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

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
            if ($this->logger) {
                $this->logger->notice('Item provided is not an AbstractImportItem');
            }
        }
    }
}
