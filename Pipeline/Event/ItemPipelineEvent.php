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
 * Event fired by the Pipeline containing item currently being processed.
 */
class ItemPipelineEvent extends Event
{
    use ContextAwareTrait;

    /**
     * @var mixed
     */
    private $item;

    /**
     * @var mixed
     */
    private $output;

    /**
     * @param mixed $item
     */
    public function __construct($item)
    {
        $this->setItem($item);
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
}
