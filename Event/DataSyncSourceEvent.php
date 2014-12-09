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

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\DiffProvider\DiffProvider;

/**
 * Data Sync SourceEvent.
 */
class DataSyncSourceEvent
{
    /**
     * @var DiffProvider
     */
    private $provider;

    /**
     * Dependency injection.
     *
     * @param DiffProvider $provider
     */
    public function __construct(DiffProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Returns diff provider.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource($this->provider);
    }
}
