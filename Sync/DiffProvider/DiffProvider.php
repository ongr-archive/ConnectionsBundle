<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider;

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;

/**
 * Responsible for collecting sync data from data source for data import and event firing.
 */
abstract class DiffProvider implements \Iterator
{
    /**
     * Event listener for diff provider pipeline.
     *
     * @param SourcePipelineEvent $event
     */
    abstract public function onSource(SourcePipelineEvent $event);
}
