<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Service;

/**
 * Class ShopService.
 */
class ShopService
{
    /**
     * @var string
     */
    private $activeShop;

    /**
     * @var array
     */
    private $shops;

    /**
     * @param string $activeShop
     * @param array  $shops
     */
    public function __construct($activeShop, $shops)
    {
        $this->activeShop = $activeShop;
        $this->shops = $shops;
    }

    /**
     * @return string
     */
    public function getActiveShop()
    {
        return $this->activeShop;
    }

    /**
     * @return array
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param string $shop
     *
     * @return mixed
     */
    public function getShop($shop)
    {
        return $this->shops[$shop];
    }

    /**
     * @param string $shop
     *
     * @return string
     */
    public function getShopId($shop)
    {
        return $this->shops[$shop]['shop_id'];
    }

    /**
     * @return string
     */
    public function getActiveShopId()
    {
        return $this->shops[$this->activeShop]['shop_id'];
    }
}
