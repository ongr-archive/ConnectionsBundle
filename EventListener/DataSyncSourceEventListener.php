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

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\DiffProvider\AbstractDiffProvider;

/**
 * DataSyncSourceEventListener - adds provider to SourcePipelineEvent.
 */
class DataSyncSourceEventListener
{
    /**
     * @var AbstractDiffProvider
     */
    private $diffProvider;

    /**
     * Dependency injection.
     *
     * @param AbstractDiffProvider $provider
     */
    public function __construct(AbstractDiffProvider $provider = null)
    {
        $this->diffProvider = $provider;
    }

    /**
     * Returns diff provider.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource($this->getDiffProvider());
    }

    /**
     * @return AbstractDiffProvider
     */
    public function getDiffProvider()
    {
        if ($this->diffProvider === null) {
            throw new \LogicException('Provider must be set before using \'getProvider\'');
        }

        return $this->diffProvider;
    }

    /**
     * @param AbstractDiffProvider $diffProvider
     *
     * @return $this
     */
    public function setDiffProvider(AbstractDiffProvider $diffProvider)
    {
        $this->diffProvider = $diffProvider;

        return $this;
    }
}
