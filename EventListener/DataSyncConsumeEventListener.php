<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\EventListener;

use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\Extractor\ExtractorInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Data Sync ConsumeEvent.
 */
class DataSyncConsumeEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

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
