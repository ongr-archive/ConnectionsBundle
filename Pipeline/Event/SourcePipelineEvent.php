<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event used by Pipeline to allow registering data source iterators.
 */
class SourcePipelineEvent extends Event
{
    use ContextAwareTrait;

    /**
     * @var \Traversable[]|array[]
     */
    private $sources = [];

    /**
     * @return \Traversable[]|array[]
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @param \Iterator[]|array[] $sources
     */
    public function setSources($sources)
    {
        $this->sources = [];

        foreach ($sources as $source) {
            $this->addSource($source);
        }
    }

    /**
     * @param \Traversable|array $source
     *
     * @throws \InvalidArgumentException
     */
    public function addSource($source)
    {
        if (!is_array($source) && !($source instanceof \Traversable)) {
            throw new \InvalidArgumentException('source must be of type \Traversable|array');
        }

        $this->sources[] = $source;
    }
}
