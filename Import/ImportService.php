<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Import;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;

/**
 * ImportService class - creates pipeline for the import process and executes it.
 */
class ImportService
{
    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * Runs import process.
     *
     * @param string $target
     */
    public function import($target = null)
    {
        if ($target === null) {
            $target = 'default';
        }
        $pipeline = $this->getPipelineFactory()->create(
            "import.$target"
        );

        $pipeline->execute();
    }

    /**
     * @return PipelineFactory
     */
    public function getPipelineFactory()
    {
        return $this->pipelineFactory;
    }

    /**
     * @param PipelineFactory $pipelineFactory
     */
    public function setPipelineFactory($pipelineFactory)
    {
        $this->pipelineFactory = $pipelineFactory;
    }
}
