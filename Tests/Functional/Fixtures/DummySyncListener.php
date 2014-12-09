<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures;

use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;

/**
 * Dummy event listener to ensure event was dispatched.
 */
class DummySyncListener
{
    /**
     * @var bool Flag for testing.
     */
    private $isCalled = false;

    /**
     * Listen to downloader event.
     *
     * @param SyncTaskCompleteEvent $event
     */
    public function onComplete(
        /** @noinspection PhpUnusedParameterInspection */
        SyncTaskCompleteEvent $event
    ) {
        $this->isCalled = true;
    }

    /**
     * @return bool
     */
    public function isCalled()
    {
        return $this->isCalled;
    }
}
