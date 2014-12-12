<?php

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Pipeline\AbstractPipelineExecuteService;

/**
 * SyncImportService class.
 */
class SyncExecuteService extends AbstractPipelineExecuteService
{
    /**
     * Runs import process.
     *
     * @param string $target
     *
     * @return void
     */
    public function import($target = null)
    {
        $this->executePipeline('sync.execute.', $target);
    }
}
