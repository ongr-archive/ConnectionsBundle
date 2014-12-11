<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline;

/**
 * Simplifies logger usage.
 */
trait PipelineExecuterTrait
{
    /**
     * Prepares pipeline name and executes pipeline.
     *
     * @param string $prefix
     * @param string $target
     *
     * @return string
     */
    private function preparePipelineName($prefix, $target)
    {
        if ($target === null) {
            $target = 'default';
        }

        return $prefix . $target;
    }
}
