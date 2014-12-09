<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\EventListener;

use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;
use ONGR\ConnectionsBundle\EventListener\SyncTaskCompleteBlockerListener;

/**
 * Test for SyncTaskCompleteBlockerListener.
 */
class SyncTaskCompleteBlockerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if halt setter and getter works as expected.
     */
    public function testHaltSetter()
    {
        $listener = new SyncTaskCompleteBlockerListener();

        // Test default.
        $this->assertFalse($listener->isHalt());

        // Test setter.
        $listener->setHalt(true);
        $this->assertTrue($listener->isHalt());
    }

    /**
     * Test if event is handled as expected.
     */
    public function testHandleEvent()
    {
        $event = new SyncTaskCompleteEvent();
        $listener = new SyncTaskCompleteBlockerListener();

        // Shouldn't be stopped.
        $listener->handleEvent($event);
        $this->assertFalse($event->isPropagationStopped());

        // Should be stopped.
        $listener->setHalt(true);
        $listener->handleEvent($event);
        $this->assertTrue($event->isPropagationStopped());
    }
}
