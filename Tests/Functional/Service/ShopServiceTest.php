<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Service;

use ONGR\ConnectionsBundle\Service\ShopService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ShopServiceTest.
 */
class ShopServiceTest extends KernelTestCase
{
    /**
     * Tests Shop service.
     */
    public function testService()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        /** @var ShopService $service */
        $service = $container->get('ongr_connections.shop_service');
        $this->assertInstanceOf('ONGR\ConnectionsBundle\Service\ShopService', $service);
        $this->assertEquals('default', $service->getActiveShop());
        $this->assertEquals('0', $service->getActiveShopId());
        $this->assertEquals('12345', $service->getShopId('shop_12345'));
        $this->assertEquals(['shop_id' => '1'], $service->getShop('other'));
        $this->assertEquals(
            [
                'default' => ['shop_id' => '0'],
                'other' => ['shop_id' => '1'],
                'shop_12345' => ['shop_id' => '12345'],
            ],
            $service->getShops()
        );
    }
}
