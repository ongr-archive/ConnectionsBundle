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
use ONGR\ConnectionsBundle\Pipeline\ItemSkipException;
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
        // Modifier skips items which are string 'skip', anything else gets consumed.
        return [
            // Case #0: No data so then should be 0 consumed and skipped items.
            [[], 0, 0],
            // Case #1: All data should be consumed so 1 consume and 0 skips.
            [['consume'], 1, 0],
            // Case #2: All data should be skipped so 0 consumes and 1 skip.
            [['skip'], 0, 1],
            // Case #3: Data with consume and skips. 1 consume and 2 skips.
            [['skip', 'consume', 'skip'], 1, 2],
            // Case #3: Data with consumes and skip. 2 consumes and 1 skip.
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

        $consumer = new PipelineTestConsumer();

        $source = function (SourcePipelineEvent $event) use ($data) {
            $event->addSource($data);
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
    }

    /**
     * OnModify.
     *
     * @param ItemPipelineEvent $event
     *
     * @throws ItemSkipException
     */
    public function onModify(ItemPipelineEvent $event)
    {
        if ($event->getItem() == 'skip') {
            throw new ItemSkipException();
        }
    }
}
