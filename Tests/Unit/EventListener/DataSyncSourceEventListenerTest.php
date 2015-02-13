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

use ONGR\ConnectionsBundle\EventListener\DataSyncSourceEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\DiffProvider\AbstractDiffProvider;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DataSyncSourceEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractDiffProvider|MockObject
     */
    private $diffProvider;

    /**
     * @var DataSyncSourceEventListener
     */
    private $listener;

    /**
     * Setup services for tests.
     */
    protected function setUp()
    {
        $this->diffProvider = $this->getMock('ONGR\ConnectionsBundle\Sync\DiffProvider\AbstractDiffProvider');
        $this->listener = new DataSyncSourceEventListener($this->diffProvider);
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

        $this->listener->onSource($sourcePipelineEvent);
    }
}
