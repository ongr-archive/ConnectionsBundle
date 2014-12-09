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

use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;

/**
 * This class waits for complete tasks and stops propagation if set so.
 */
class SyncTaskCompleteBlockerListener
{
    /**
     * @var bool Should propagation be stopped or not.
     */
    private $halt = false;

    /**
     * @param bool $halt
     */
    public function setHalt($halt)
    {
        $this->halt = $halt;
    }

    /**
     * @return bool
     */
    public function isHalt()
    {
        return $this->halt;
    }

    /**
     * Handles SyncTaskCompleteEvent and stops propagation if needed.
     *
     * @param SyncTaskCompleteEvent $event
     */
    public function handleEvent(SyncTaskCompleteEvent $event)
    {
        if ($this->halt) {
            $event->stopPropagation();
        }
    }
}
