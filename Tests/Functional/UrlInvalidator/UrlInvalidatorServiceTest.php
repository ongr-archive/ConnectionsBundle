<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\UrlInvalidator;

use ONGR\ConnectionsBundle\Tests\Functional\AbstractTestCase;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\SeoDocument;
use ONGR\ConnectionsBundle\UrlInvalidator\UrlInvalidatorService;
use ONGR\RouterBundle\Document\UrlObject;

/**
 * Functional test for url invalidator service.
 */
class UrlInvalidatorServiceTest extends AbstractTestCase
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

        $urlObject1 = new UrlObject();
        $urlObject1->setUrl('test-url-1.html');
        $urlObject1->setKey('t1');

        $urlObject2 = new UrlObject();
        $urlObject2->setUrl('test-url-2.html');
        $urlObject2->setKey('t2');

        $document = new SeoDocument();
        $document->setUrls([$urlObject1, $urlObject2]);

        $service->loadUrlsFromDocument('content', $document);

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
