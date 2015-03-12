<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Crawler\Command;

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Command\RepositoryCrawlerCommand;
use ONGR\ConnectionsBundle\Pipeline\PipelineStarter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RepositoryCrawlerCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ongr:repository-crawler:crawl progress helper behavior.
     */
    public function testCommandProgress()
    {
        $document1 = $this->getMockForAbstractClass('ONGR\ElasticsearchBundle\Document\DocumentInterface');

        $source = $this->getMockForAbstractClass('ONGR\ConnectionsBundle\EventListener\AbstractCrawlerSource');

        $modifier = $this->getMockForAbstractClass('ONGR\ConnectionsBundle\EventListener\AbstractCrawlerModifier');

        $container = new ContainerBuilder();

        $command = new RepositoryCrawlerCommand();
        $command->setContainer($container);

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->any())->method('getOption')->with('action-name')->will(
            $this->returnValue('default')
        );

        $writes = 0;
        $callback = function () use (&$writes) {
            $writes++;
        };

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $outputFormatter = $this->getMock('Symfony\Component\Console\Formatter\OutputFormatterInterface');
        $output->expects($this->any())->method('write')->will($this->returnCallback($callback));
        $output->expects($this->any())->method('isDecorated')->will($this->returnValue(true));
        $output->expects($this->any())->method('getFormatter')->will($this->returnValue($outputFormatter));

        $itemEvent = new ItemPipelineEvent($document1);

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                ['ongr.pipeline.repository_crawler.default.source', $this->anything()],
                ['ongr.pipeline.repository_crawler.default.start', $this->anything()],
                ['ongr.pipeline.repository_crawler.default.finish', $this->anything()],
                ['ongr.pipeline.repository_crawler.default.modify', $this->anything()]
            )
            ->willReturnOnConsecutiveCalls(
                ($this->returnValue($source->onSource(new SourcePipelineEvent()))),
                ($this->returnValue(null)),
                ($this->returnValue(null)),
                ($this->returnValue($modifier->onModify($itemEvent)))
            );

        $pipelineFactory = new PipelineFactory();

        $pipelineFactory->setClassName('\ONGR\ConnectionsBundle\Pipeline\Pipeline');
        $pipelineFactory->setDispatcher($dispatcher);

        $crawler = new PipelineStarter();
        $crawler->setPipelineFactory($pipelineFactory);

        $container->set('ongr_connections.repository_crawler_service', $crawler);

        $command->run($input, $output);

        if ($writes < 2) {
            $this->fail(
                "Console output was expected to be written at least 2 times, actually written {$writes} times."
            );
        }
    }
}
