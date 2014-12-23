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

use ONGR\ConnectionsBundle\Command\SyncParametersCommand;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ONGR\ConnectionsBundle\Service\PairStorage;

class SyncParametersCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PairStorage|MockObject
     */
    private $pairStorage;

    /**
     * Setup services before tests.
     */
    protected function setUp()
    {
        $this->pairStorage = $this->getMockBuilder('ONGR\ConnectionsBundle\Service\PairStorage')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test ongr:sync:provide:parameter behavior.
     */
    public function testCommand()
    {
        $parameter = 'winner';
        $parameter_value = 'Red Viper';
        $parameter_new_value = 'The Mountain';

        $this->pairStorage->expects($this->once())
            ->method('get')
            ->with($this->equalTo($parameter))
            ->willReturn($parameter_value);

        $this->pairStorage->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($parameter),
                $this->equalTo($parameter_new_value)
            );

        $container = new ContainerBuilder();
        $container->set('ongr_connections.pair_storage', $this->pairStorage);

        $command = new SyncParametersCommand();
        $command->setContainer($container);
        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('ongr:sync:provide:parameter');
        $commandTester = new CommandTester($commandForTesting);

        $commandTester->execute(
            [
                'command' => $command->getName(),
                'parameter' => $parameter,
                'value' => $parameter_new_value,
                '--set' => true,
            ]
        );
        $this->assertContains('New value written: \'The Mountain\'', $commandTester->getDisplay());
    }
}
