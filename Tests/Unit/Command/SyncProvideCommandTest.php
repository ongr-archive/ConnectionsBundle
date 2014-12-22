<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Command;

use ONGR\ConnectionsBundle\Command\SyncProvideCommand;
use ONGR\ConnectionsBundle\Pipeline\PipelineStarter;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DeleteDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DiffItemFactory;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\Extractor\ActionTypes;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SyncProvideCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PipelineStarter|MockObject
     */
    private $pipelineStarter;

    /**
     * Setup services before tests.
     */
    protected function setUp()
    {
        $this->pipelineStarter = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\PipelineStarter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test ongr:sync:storage:create behavior.
     */
    public function testCommand()
    {
        $targetName = 'some-target';

        $this->pipelineStarter->expects($this->once())
            ->method('startPipeline')
            ->with('data_sync.', $targetName);

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync.data_sync_service', $this->pipelineStarter);

        $command = new SyncProvideCommand();
        $command->setContainer($container);
        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('ongr:sync:provide');
        $commandTester = new CommandTester($commandForTesting);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'target' => $targetName,
            ]
        );

        $this->assertContains('Job finished', $commandTester->getDisplay());
    }

    /**
     * Test diff item factory.
     */
    public function testCreateDefaultCase()
    {
        $diffItemFactory = new DiffItemFactory();

        $this->assertEquals(new CreateDiffItem(), $diffItemFactory->create(ActionTypes::CREATE));
        $this->assertEquals(new UpdateDiffItem(), $diffItemFactory->create(ActionTypes::UPDATE));
        $this->assertEquals(new DeleteDiffItem(), $diffItemFactory->create(ActionTypes::DELETE));

        $this->setExpectedException('InvalidArgumentException');
        $diffItemFactory->create(null);
    }
}
