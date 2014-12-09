<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Command;

use ONGR\ConnectionsBundle\Service\UrlInvalidatorService;
use ONGR\ConnectionsBundle\Tests\Model\ProductModel;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;

/**
 * Functional test for url invalidator service.
 */
class UrlInvalidatorServiceTest extends TestBase
{
    /**
     * @return array
     */
    public function getTestInvalidateData()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Test if service is created and cache reset works after invalidation.
     *
     * @param bool $invalidateSeo Flag passed to UrlInvalidatorService.
     *
     * @dataProvider    getTestInvalidateData()
     */
    public function testInvalidate($invalidateSeo)
    {
        /** @var UrlInvalidatorService $service */
        $service = self::createClient()->getContainer()->get('ongr_connections.url_invalidator_service');

        $service->setInvalidateSeoUrls($invalidateSeo);

        $doc = new ProductModel(null);
        $doc->url = [
            ['url' => 'test-url-1.html', 'key' => 't1'],
            ['url' => 'test-url-2.html', 'key' => 't2'],
        ];

        $service->loadUrlsFromDocument('content', $doc);

        $shouldBeInvalidated = [];

        if ($invalidateSeo) {
            $shouldBeInvalidated = [
                'test-url-1.html',
                'test-url-2.html',
            ];
        }

        $this->assertEquals($shouldBeInvalidated, $service->getUrls());

        $service->invalidate();

        $this->assertEquals([], $service->getUrls());
    }
}
