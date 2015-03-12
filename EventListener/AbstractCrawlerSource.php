<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\ConnectionsBundle\EventListener;

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;

/**
 * Provides data from Elasticsearch repository.
 */
abstract class AbstractCrawlerSource
{
    /**
     * Source provider event.
     *
     * @param SourcePipelineEvent $sourceEvent
     */
    abstract public function onSource(SourcePipelineEvent $sourceEvent);
}
