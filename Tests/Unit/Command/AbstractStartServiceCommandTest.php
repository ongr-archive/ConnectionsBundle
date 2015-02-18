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

use ONGR\ConnectionsBundle\Command\AbstractStartServiceCommand;
use ONGR\ConnectionsBundle\Pipeline\PipelineStarter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractStartServiceCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests AbstractStartServiceCommand.
     */
    public function testAbstractStartServiceCommand()
    {
        /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface');
        /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject $input */
        $input = $this->getMockForAbstractClass('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->once())->method('getArgument')->with('target')->willReturn('target');

        $factory = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\PipelineFactory')
            ->setMethods(['setProgressBar'])
            ->getMock();
        $factory
            ->method('setProgressBar')
            ->will($this->returnValue(null));

        /** @var PipelineStarter|\PHPUnit_Framework_MockObject_MockObject $PipelineStarter */
        $pipelineStarter = $this->getMock('ONGR\ConnectionsBundle\Pipeline\PipelineStarter');

        $pipelineStarter->setPipelineFactory($factory);

        $pipelineStarter->method('getPipelineFactory')
            ->will($this->returnValue($factory));

        $pipelineStarter->expects($this->once())
            ->method('startPipeline')
            ->with('prefix', 'target');

        /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('get')
            ->with('PipelineStarterService')
            ->willReturn($pipelineStarter);

        /** @var AbstractStartServiceCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMockBuilder('ONGR\ConnectionsBundle\Command\AbstractStartServiceCommand')
            ->setConstructorArgs(['name', 'description'])
            ->setMethods(['addArgument', 'getContainer'])
            ->getMock();

        $command->expects($this->once())->method('addArgument')->with(
            'target',
            InputArgument::OPTIONAL,
            $this->anything()
        );
        $command->expects($this->once())
            ->method('getContainer')->willReturn($container);

        $reflection = new \ReflectionClass($command);

        $method = $reflection->getMethod('configure');
        $method->setAccessible(true);
        $method->invoke($command);

        $method = $reflection->getMethod('start');
        $method->setAccessible(true);

        $method->invoke($command, $input, $output, 'PipelineStarterService', 'prefix');

        $this->assertEquals('description', $command->getDescription());
    }
}
