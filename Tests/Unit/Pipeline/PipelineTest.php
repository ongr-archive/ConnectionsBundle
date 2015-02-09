<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Pipeline;

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\ItemSkipper;
use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PipelineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Pipeline data provider.
     *
     * @return array
     */
    public function pipelineData()
    {
        // Modifier skips items which are equal to 'skip', anything else gets consumed.
        return [
            // Case #0: No data. Results should be: 0 consumed and skipped items.
            [[], 0, 0],
            // Case #1: All data should be consumed, so 1 consume and 0 skips.
            [['consume'], 1, 0],
            // Case #2: All data should be skipped, so 0 consumes and 1 skip.
            [['skip'], 0, 1],
            // Case #3: Data with consume and skips. 1 consume and 2 skips.
            [['skip', 'consume', 'skip'], 1, 2],
            // Case #4: Data with consumes and skip. 2 consumes and 1 skip.
            [['consume', 'skip', 'consume'], 2, 1],
        ];
    }

    /**
     * Tests pipeline.
     *
     * @param array $data
     * @param int   $expectedConsumedItems
     * @param int   $expectedSkippedItems
     *
     * @dataProvider PipelineData
     */
    public function testPipeline($data, $expectedConsumedItems, $expectedSkippedItems)
    {
        $pipelineFactory = new PipelineFactory();
        $pipelineFactory->setDispatcher(new EventDispatcher());
        $pipelineFactory->setClassName('ONGR\ConnectionsBundle\Pipeline\Pipeline');
        $expectedContext = 'This is a test of context';
        $consumer = new PipelineTestConsumer();

        $source = function (SourcePipelineEvent $event) use ($data, $expectedContext) {
            $event->addSource($data);
            $event->setContext($expectedContext);
        };

        $pipeline = $pipelineFactory->create(
            'test',
            [
                'sources' => [$source],
                'modifiers' => [[$this, 'onModify']],
                'consumers' => [[$consumer, 'onConsume']],
            ]
        );
        $pipeline->start();

        $this->assertEquals($expectedConsumedItems, $consumer->getConsumeCalled());
        $this->assertEquals($expectedSkippedItems, $consumer->getSkipCalled());
        $this->assertEquals($expectedContext, $pipeline->getContext());
    }

    /**
     * Tests pipeline with progressbar.
     *
     * @param array $data
     * @param int   $expectedConsumedItems
     * @param int   $expectedSkippedItems
     *
     * @dataProvider PipelineData
     */
    public function testPipelineProgress($data, $expectedConsumedItems, $expectedSkippedItems)
    {
        $pipelineFactory = new PipelineFactory();
        $pipelineFactory->setDispatcher(new EventDispatcher());
        $pipelineFactory->setClassName('ONGR\ConnectionsBundle\Pipeline\Pipeline');
        $expectedContext = 'This is a test of context';

        $progressBar = $this
            ->getMockBuilder('Symfony\Component\Console\Helper\ProgressBar')
            ->disableOriginalConstructor()
            ->getMock();
        $progressBar->expects($this->once())->method('start');
        $progressBar->expects($this->exactly(count($data)))->method('advance');
        $progressBar->expects($this->once())->method('finish');

        $consumer = new PipelineTestConsumer();

        $source = function (SourcePipelineEvent $event) use ($data, $expectedContext) {
            $event->addSource($data);
            $event->setContext($expectedContext);
        };

        $pipeline = $pipelineFactory->create(
            'test',
            [
                'sources' => [$source],
                'modifiers' => [[$this, 'onModify']],
                'consumers' => [[$consumer, 'onConsume']],
            ]
        );

        $pipeline->setProgressBar($progressBar);

        $pipeline->start();

        $this->assertEquals($expectedConsumedItems, $consumer->getConsumeCalled());
        $this->assertEquals($expectedSkippedItems, $consumer->getSkipCalled());
        $this->assertEquals($expectedContext, $pipeline->getContext());
    }

    /**
     * OnModify.
     *
     * @param ItemPipelineEvent $event
     */
    public function onModify(ItemPipelineEvent $event)
    {
        if ($event->getItem() == 'skip') {
            ItemSkipper::skip($event, 'Test reason for skip');
        }
    }
}
