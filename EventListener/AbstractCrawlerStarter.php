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
 * Advances progress when applicable.
 */
abstract class AbstractCrawlerStarter
{
    /**
     * Events onStart action.
     *
     * @param ItemPipelineEvent $documentEvent
     *
     * @throws \LogicException
     */
    abstract public function onStart(ItemPipelineEvent $documentEvent);
}
