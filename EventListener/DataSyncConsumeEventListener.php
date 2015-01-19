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
 * DataSyncConsumeEventListener - extracts item onConsume event.
 */
class DataSyncConsumeEventListener extends AbstractConsumeEventListener implements LoggerAwareInterface
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
    public function consume(ItemPipelineEvent $event)
    {
        $this->extractor->extract($event->getItem());
    }
}
