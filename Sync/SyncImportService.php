<?php

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Import\AbstractImportService;

/**
 * SyncImportService class.
 */
class SyncImportService extends AbstractImportService
{
    /**
     * {@inheritdoc}
     */
    public function import($target = null)
    {
        $this->executePipeline('sync.import.', $target);
    }
}
