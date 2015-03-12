<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\UrlInvalidator;

use ONGR\ConnectionsBundle\UrlInvalidator\DocumentUrlCollectorInterface;
use ONGR\ConnectionsBundle\UrlInvalidator\UrlInvalidatorService;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\RouterBundle\Document\SeoAwareInterface;
use ONGR\RouterBundle\Document\UrlObject;
use ReflectionClass;
use Symfony\Component\Routing\RouterInterface;

/**
 * Unit test for UrlInvalidatorService.
 */
class UrlInvalidatorServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Setup mock for test.
     */
    public function setUp()
    {
        $this->manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])
            ->getMock();
        $this->manager->expects($this->any())->method('getRepository')->willReturnSelf();
    }

    /**
     * @param bool $invalidateSeo Flag passed to UrlInvalidatorService.
     *
     * @return UrlInvalidatorService
     */
    protected function getUrlInvalidatorServiceMock($invalidateSeo = false)
    {
        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $service = new UrlInvalidatorService(
            $router,
            $this->manager,
            'http://ongr.dev',
            100,
            '',
            '',
            $invalidateSeo
        );

        return $service;
    }

    /**
     * Get fake url collector.
     *
     * @param array $documentUrls
     * @param array $documentParams
     * @param array $typeUrls
     *
     * @return DocumentUrlCollectorInterface
     */
    protected function getDocumentUrlCollectorMock(
        $documentUrls = [],
        $documentParams = [],
        $typeUrls = []
    ) {
        $collector = $this->getMock('ONGR\ConnectionsBundle\UrlInvalidator\DocumentUrlCollectorInterface');

        $collector->expects($this->any())->method('getDocumentUrls')->will($this->returnValue($documentUrls));
        $collector->expects($this->any())->method('getDocumentParameters')->will($this->returnValue($documentParams));
        $collector->expects($this->any())->method('getUrlsByType')->will($this->returnValue($typeUrls));

        return $collector;
    }

    /**
     * Gets SeoAware document mock.
     *
     * @param array $urls
     *
     * @return SeoAwareInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSeoDocumentMock(array $urls = [])
    {
        $urlObjects = [];
        foreach ($urls as $key => $url) {
            /** @var UrlObject|\PHPUnit_Framework_MockObject_MockObject $urlObject */
            $urlObject = $this->getMock('ONGR\RouterBundle\Document\UrlObject');
            $urlObject->expects($this->any())->method('getUrl')->willReturn($url);
            $urlObject->expects($this->any())->method('getKey')->willReturn($key);

            $urlObjects[] = $urlObject;
        }

        /** @var SeoAwareInterface|\PHPUnit_Framework_MockObject_MockObject $document */
        $document = $this->getMockForAbstractClass('ONGR\RouterBundle\Document\SeoAwareInterface');
        $document->expects($this->any())->method('getUrls')->willReturn($urlObjects);

        return $document;
    }

    /**
     * Test if documents are fetched correctly.
     */
    public function testDocumentUrls()
    {
        $service = $this->getUrlInvalidatorServiceMock();

        $document = $this->getSeoDocumentMock(
            [
                't1' => 'test-url-1.html',
                't2' => 'test-url-2.html',
            ]
        );

        $service->loadUrlsFromDocument('content', $document);
        $file = $service->createUrlsTempFile();

        $this->assertStringEqualsFile($file, '');

        unlink($file);
    }

    /**
     * Test document specific urls.
     */
    public function testDocumentSpecificUrls()
    {
        $service = $this->getUrlInvalidatorServiceMock();

        $document = $this->getSeoDocumentMock(
            [
                't1' => 'test-url-1.html',
                't2' => 'test-url-2.html',
            ]
        );

        $collector = $this->getDocumentUrlCollectorMock(
            ['collector-generated-url-1.html']
        );

        $service->addUrlCollector($collector);
        $service->loadUrlsFromDocument('content', $document);
        $file = $service->createUrlsTempFile();

        $this->assertStringEqualsFile(
            $file,
            implode(
                PHP_EOL,
                ['http://ongr.dev/collector-generated-url-1.html']
            )
        );

        unlink($file);
    }

    /**
     * Test document specific urls.
     */
    public function testTypeSpecificUrls()
    {
        $service = $this->getUrlInvalidatorServiceMock();

        $document = $this->getSeoDocumentMock(
            [
                't1' => 'test-url-1.html',
                't2' => 'test-url-2.html',
            ]
        );

        $collector = $this->getDocumentUrlCollectorMock(
            [],
            [],
            [
                'collector-type-generated-url-1.html',
                'test-url-2.html',
            ]
        );

        $service->addUrlCollector($collector);
        $service->loadUrlsFromDocument('content', $document);
        $service->loadUrlsByType('test');
        $file = $service->createUrlsTempFile();

        $this->assertStringEqualsFile(
            $file,
            implode(
                PHP_EOL,
                [
                    'http://ongr.dev/collector-type-generated-url-1.html',
                    'http://ongr.dev/test-url-2.html',
                ]
            )
        );

        unlink($file);
    }

    /**
     * Unit test to check whether default SEO url are invalidated if true falg is passed to UrlInvalidatorService.
     */
    public function testIvalidateDefaultUrls()
    {
        $service = $this->getUrlInvalidatorServiceMock(true);

        $document = $this->getSeoDocumentMock(
            [
                't1' => 'test-url-1.html',
                't2' => 'test-url-2.html',
            ]
        );

        $service->loadUrlsFromDocument('content', $document);
        $file = $service->createUrlsTempFile();

        $this->assertStringEqualsFile(
            $file,
            implode(
                PHP_EOL,
                [
                    'http://ongr.dev/test-url-1.html',
                    'http://ongr.dev/test-url-2.html',
                ]
            )
        );

        unlink($file);
    }

    /**
     * Change visibility for protected methods.
     *
     * @param string $className
     * @param string $method
     * @param null   $property
     *
     * @return object
     */
    protected function changeMethodType($className, $method = null, $property = null)
    {
        $class = new ReflectionClass($className);

        if ($method !== null) {
            $method = $class->getMethod($method);
            $method->setAccessible(true);
        }

        if ($property !== null) {
            $property = $class->getProperty($property);
            $property->setAccessible(true);
        }

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $instance = new $className(
            $router,
            $this->manager,
            'http://ongr.dev',
            100,
            '',
            '',
            true
        );

        return [$method, $instance, $property];
    }

    /**
     * Test methods that needs mock.
     */
    public function testInternals()
    {
        $className = 'ONGR\ConnectionsBundle\UrlInvalidator\UrlInvalidatorService';

        // Test protected addWildcard.
        $mockAddWildcard = $this->changeMethodType($className, 'addWildcard');
        $testItems = [
            ['/test/', '/test/*'],
            ['/test', '/test'],
        ];

        foreach ($testItems as $item) {
            $mockAddWildcard[0]->invokeArgs($mockAddWildcard[1], [ & $item[0]]);
            $this->assertEquals($item[0], $item[1]);
        }

        // Test protected setInvalidateSeoUrls.
        $mockSeoUrls = $this->changeMethodType($className, 'setInvalidateSeoUrls', 'invalidateSeoUrls');
        $seoItem = 'item';
        $mockSeoUrls[0]->invokeArgs($mockSeoUrls[1], [$seoItem]);

        $this->assertEquals($mockSeoUrls[2]->getValue($mockSeoUrls[1]), $seoItem);

        // Test addDocumentParameter.
        $mockDocParam = $this->changeMethodType($className, 'addDocumentParameter', 'documentParamCache');
        $field = 'field';
        $value = 'value';

        $mockDocParam[0]->invokeArgs($mockDocParam[1], [$field, $value]);

        $md5Key = md5($value . $field);
        $array = $mockDocParam[2]->getValue($mockDocParam[1]);

        $this->assertEquals($array[$md5Key], [$field, $value]);

        // Test resetCache.
        $mockResetCache = $this->changeMethodType($className, 'resetCache', 'invalidateSeoUrls');
        $mockResetCache[2]->setValue($mockResetCache[1], ['a', 'b']);
        $mockResetCache[0]->invokeArgs($mockResetCache[1], []);
    }
}
