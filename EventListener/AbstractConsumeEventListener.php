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

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;

/**
 * AbstractConsumeEventListener class.
 */
abstract class AbstractConsumeEventListener
{
    /**
     * Entry point of consume event.
     *
     * @param ItemPipelineEvent $event
     */
    public function onConsume(ItemPipelineEvent $event)
    {
        if ($event->getItemSkipException()) {
            $this->skip($event);
        } else {
            $this->consume($event);
        }
    }

    /**
     * Called when item should be skipped.
     *
     * @param ItemPipelineEvent $event
     */
    public function skip(ItemPipelineEvent $event)
    {
    }

    /**
     * Called when item should be consumed.
     *
     * @param ItemPipelineEvent $event
     */
    abstract public function consume(ItemPipelineEvent $event);
}
