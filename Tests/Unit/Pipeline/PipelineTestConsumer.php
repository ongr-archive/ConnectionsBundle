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

use ONGR\ConnectionsBundle\EventListener\AbstractConsumeEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;

/**
 * PipelineTestConsumer class.
 */
class PipelineTestConsumer extends AbstractConsumeEventListener
{
    /**
     * @var int
     */
    private $consumeCalled = 0;

    /**
     * @var int
     */
    private $skipCalled = 0;

    /**
     * {@inheritdoc}
     */
    public function consume(ItemPipelineEvent $event)
    {
        $this->consumeCalled++;
    }

    /**
     * {@inheritdoc}
     */
    public function skip(ItemPipelineEvent $event)
    {
        if ($event->getItemSkip()->getReason() === 'Test reason for skip') {
            $this->skipCalled++;
        }
    }

    /**
     * @return int
     */
    public function getConsumeCalled()
    {
        return $this->consumeCalled;
    }

    /**
     * @return int
     */
    public function getSkipCalled()
    {
        return $this->skipCalled;
    }
}
