<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs;

/**
 * Trait used for various sync types.
 */
trait SyncTrait
{
    /**
     * @var string The current shop used.
     */
    protected $activeShop;

    /**
     * @var array Shop list for multishop.
     */
    protected $shops;

    /**
     * Sets active shop.
     *
     * If multi shop is used, it will be used to find out which shop to update.
     *
     * @param string $activeShop
     */
    public function setActiveShop($activeShop)
    {
        $this->activeShop = $activeShop;
    }

    /**
     * Set shops if its a multi shop.
     *
     * @param array $shops
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
    }
}
