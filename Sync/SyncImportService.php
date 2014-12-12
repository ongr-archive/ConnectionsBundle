<?php

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Pipeline\AbstractPipelineExecuteService;

/**
 * SyncImportService class.
 */
class SyncImportService extends AbstractPipelineExecuteService
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
        $this->executePipeline('sync.import.', $target);
    }
}
