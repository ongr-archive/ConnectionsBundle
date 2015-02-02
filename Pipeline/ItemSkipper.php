<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline;

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;

/**
 * Provides a static method for skipping unwanted items.
 */
class ItemSkipper
{
    /**
     * Skips item, marks event as skipped with a reason.
     *
     * @param ItemPipelineEvent $event
     * @param string            $reason
     */
    public static function skip(ItemPipelineEvent $event, $reason = '')
    {
        $itemSkip = new ItemSkip();
        $itemSkip->setReason($reason);
        $event->setItemSkip($itemSkip);
        $event->stopPropagation();
    }
}
