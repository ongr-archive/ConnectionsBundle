<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\UrlInvalidator;

use ONGR\ConnectionsBundle\Pipeline\Event\FinishPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;

/**
 * Base class for url invalidation for pipeline items.
 */
abstract class AbstractItemUrlInvalidator
{
    /**
     * @var UrlInvalidatorService
     */
    private $urlInvalidator;

    /**
     * @return UrlInvalidatorService
     */
    public function getUrlInvalidator()
    {
        return $this->urlInvalidator;
    }

    /**
     * @param UrlInvalidatorService $urlInvalidator
     *
     * @return static
     */
    public function setUrlInvalidator(UrlInvalidatorService $urlInvalidator)
    {
        $this->urlInvalidator = $urlInvalidator;

        return $this;
    }

    /**
     * Pipeline consume event listener.
     *
     * @param ItemPipelineEvent $event
     */
    public function onConsume(ItemPipelineEvent $event)
    {
        $this->invalidateItem($event->getItem(), $event->getContext());
    }

    /**
     * Pipeline finish event listener.
     *
     * @param FinishPipelineEvent $event
     */
    public function onFinish(FinishPipelineEvent $event)
    {
        $this->getUrlInvalidator()->invalidate();
    }

    /**
     * Invalidates urls associated with given item.
     *
     * @param mixed $item
     * @param mixed $context
     */
    abstract public function invalidateItem($item, $context = null);
}
