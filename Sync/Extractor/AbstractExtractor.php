<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\Extractor;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Common actions required for all extractors.
 */
abstract class AbstractExtractor
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Gets ids of shops from the configuration.
     *
     * @return array
     */
    protected function getShopIds()
    {
        $shopIds = [];

        try {
            $shops = $this->container->getParameter('ongr_connections.shops');
        } catch (InvalidArgumentException $e) {
            $shops = [];
        }

        foreach ($shops as $shop) {
            $shopIds[] = $shop['shop_id'];
        }

        return $shopIds;
    }
}
