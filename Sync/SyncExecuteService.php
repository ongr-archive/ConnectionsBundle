<?php

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
    public function import($target = null)
    {
        $this->executePipeline('sync.execute.', $target);
    }
}
