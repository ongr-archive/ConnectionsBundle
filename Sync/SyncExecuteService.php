<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Pipeline\AbstractPipelineExecuteService;

/**
 * SyncExecuteService - executes  sync import pipeline.
 */
class SyncExecuteService extends AbstractPipelineExecuteService
{
    /**
     * Runs import process.
     *
     * @param string $target
     */
    public function startPipeline($target = null)
    {
        $this->executePipeline('sync.execute.', $target);
    }
}