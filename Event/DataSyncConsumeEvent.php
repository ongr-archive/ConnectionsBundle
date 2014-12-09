<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\Extractor\ExtractorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Data Sync ConsumeEvent.
 */
class DataSyncConsumeEvent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * Dependency injection.
     *
     * @param ExtractorInterface $extractor
     */
    public function __construct(ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * Consumes given event.
     *
     * @param ItemPipelineEvent $event
     */
    public function onConsume(ItemPipelineEvent $event)
    {
        $this->extractor->extract($event->getItem());
    }
}
