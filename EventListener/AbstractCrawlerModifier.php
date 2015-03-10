<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\ConnectionsBundle\EventListener;

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;

/**
 * Abstract class for event listener.
 */
abstract class AbstractCrawlerModifier
{
    /**
     * Processes document.
     *
     * @param mixed $document
     */
    abstract protected function processData($document);

    /**
     * Events onModify action.
     *
     * @param ItemPipelineEvent $documentEvent
     */
    public function onModify(ItemPipelineEvent $documentEvent)
    {
        $this->processData($documentEvent->getItem());
    }
}
