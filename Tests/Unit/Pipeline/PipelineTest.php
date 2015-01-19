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

/**
 * PipelineTest class.
 */
class PipelineTest extends \PHPUnit_Framework_TestCase
{
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

    /**
     * Pipeline data provider.
     *
     * @return array
     */
    public function pipelineData()
    {
        return [
            [[], 0, 0],
            [['consume'], 1, 0],
            [['skip'], 0, 1],
            [['skip', 'consume', 'skip'], 1, 2],
            [['consume', 'skip', 'consume'], 2, 1],
        ];
    }
}
