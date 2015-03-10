<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Event;

use ONGR\ConnectionsBundle\EventListener\AbstractCrawlerModifier;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Gets called on each iteration.
 */
class CrawlerTestModifier extends AbstractCrawlerModifier
{
    /**
     * Constructor.
     *
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Processes documents.
     *
     * @param AbstractDocument $item
     */
    protected function processData($item)
    {
        echo " {$item->getId()} \n";
    }
}
