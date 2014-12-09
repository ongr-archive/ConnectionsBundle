<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Event;

use ONGR\ConnectionsBundle\Event\DataSyncSourceEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\DiffProvider\DiffProvider;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DataSyncSourceEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiffProvider|MockObject
     */
    private $diffProvider;

    /**
     * @var DataSyncSourceEvent
     */
    private $event;

    /**
     * Setup services for tests.
     */
    protected function setUp()
    {
        $this->diffProvider = $this->getMock('ONGR\ConnectionsBundle\Sync\DiffProvider\DiffProvider');
        $this->event = new DataSyncSourceEvent($this->diffProvider);
    }

    /**
     * Test onSource action.
     */
    public function testOnSource()
    {
        /** @var SourcePipelineEvent|MockObject $sourcePipelineEvent */
        $sourcePipelineEvent = $this->getMock('ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent');
        $sourcePipelineEvent->expects($this->once())
            ->method('addSource')
            ->with($this->diffProvider);

        $this->event->onSource($sourcePipelineEvent);
    }
}
