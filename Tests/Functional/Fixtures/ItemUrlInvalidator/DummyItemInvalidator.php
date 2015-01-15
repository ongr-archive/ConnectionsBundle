<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ItemUrlInvalidator;

use ONGR\ConnectionsBundle\Pipeline\Event\FinishPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\UrlInvalidator\AbstractItemUrlInvalidator;

/**
 * Dummy item invalidator class for tests.
 */
class DummyItemInvalidator extends AbstractItemUrlInvalidator
{
    /**
     * @var int
     */
    private $finishCalled = 0;

    /**
     * @var int
     */
    private $consumeCalled = 0;

    /**
     * Invalidates urls associated with given item.
     *
     * @param mixed $item
     * @param mixed $context
     */
    public function invalidateItem($item, $context = null)
    {
        $this->getUrlInvalidator()->addUrl($item->url);
    }

    /**
     * {@inheritdoc}
     */
    public function consume(ItemPipelineEvent $event)
    {
        $this->consumeCalled++;

        return parent::consume($event);
    }

    /**
     * {@inheritdoc}
     */
    public function onFinish(FinishPipelineEvent $event)
    {
        $this->finishCalled++;

        return parent::onFinish($event);
    }

    /**
     * @return int
     */
    public function getFinishCalled()
    {
        return $this->finishCalled;
    }

    /**
     * @return int
     */
    public function getConsumeCalled()
    {
        return $this->consumeCalled;
    }
}
