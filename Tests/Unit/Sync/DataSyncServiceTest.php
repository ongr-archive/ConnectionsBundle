<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Service;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Sync\DataSyncService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DataSyncServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockObject
     */
    private $pipeline;

    /**
     * @var PipelineFactory|MockObject
     */
    private $pipelineFactory;

    /**
     * @var DataSyncService
     */
    private $service;

    /**
     * Setup services for tests.
     */
    protected function setUp()
    {
        $this->pipeline = $this->getMock('ONGR\ConnectionsBundle\Pipeline\PipelineInterface');
        $this->pipelineFactory = $this->getMock('ONGR\ConnectionsBundle\Pipeline\PipelineFactory');
        $this->service = new DataSyncService();
        $this->service->setPipelineFactory($this->pipelineFactory);
    }

    /**
     * Test pipeline execution.
     */
    public function testStartPipeline()
    {
        $pipelineName = 'some-target';

        $this->pipeline->expects($this->once())
            ->method('execute');

        $this->pipelineFactory->expects($this->once())
            ->method('create')
            ->with('data_sync.' . $pipelineName)
            ->will($this->returnValue($this->pipeline));

        $this->service->startPipeline($pipelineName);
    }

    /**
     * Test event dispatching.
     */
    public function testPipelineEventDispatching()
    {
        $pipelineName = 'some-target';

        /** @var EventDispatcherInterface|MockObject $dispatcher */
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                ['ongr.pipeline.data_sync.' . $pipelineName . '.source', $this->anything()],
                ['ongr.pipeline.data_sync.' . $pipelineName . '.start', $this->anything()],
                ['ongr.pipeline.data_sync.' . $pipelineName . '.finish', $this->anything()],
                ['ongr.pipeline.data_sync.' . $pipelineName . '.consume', $this->anything()],
                ['ongr.pipeline.data_sync.' . $pipelineName . '.modify', $this->anything()]
            );

        $dataSyncService = new DataSyncService();

        $pipelineFactory = new PipelineFactory();
        $pipelineFactory->setDispatcher($dispatcher);
        $pipelineFactory->setClassName('ONGR\ConnectionsBundle\Pipeline\Pipeline');

        $dataSyncService->setPipelineFactory($pipelineFactory);
        $dataSyncService->startPipeline($pipelineName);
    }
}
