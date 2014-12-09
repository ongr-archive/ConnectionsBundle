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
 * Event used by Pipeline to notify that item processing is about to start.
 */
class StartPipelineEvent extends Event
{
    use ContextAwareTrait;

    /**
     * @var int
     */
    private $itemCount = null;

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     *
     * @return static
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = $itemCount;

        return $this;
    }
}
