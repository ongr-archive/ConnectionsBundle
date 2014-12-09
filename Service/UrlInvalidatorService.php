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

use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\DSL\Filter\LimitFilter;
use ONGR\ElasticsearchBundle\DSL\Query\Query;
use ONGR\ElasticsearchBundle\DSL\Query\TermQuery;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;

/**
 * Invalidates URLs.
 */
class UrlInvalidatorService
{
    /**
     * @var array URLs to invalidate.
     */
    protected $urlCache;

    /**
     * @var array Values and field of documents whose links needs to be invalidated.
     */
    protected $documentParamCache;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string Base project URL.
     */
    protected $baseUrl;

    /**
     * @var string|null Root dir path.
     */
    protected $rootDir;

    /**
     * @var string Cache script path from Root dir.
     */
    protected $cacheScript;

    /**
     * @var float CURL request timeout.
     */
    protected $curlTimeout;

    /**
     * @var DocumentUrlCollectorInterface[]
     */
    protected $urlCollectors;

    /**
     * @var bool If set to false, default seo urls will not be invalidated.
     */
    protected $invalidateSeoUrls;

    /**
     * @param RouterInterface $router
     * @param Manager         $manager
     * @param string          $baseUrl
     * @param float           $curlTimeout
     * @param string          $rootDir
     * @param string          $cacheScript
     * @param bool            $invalidateSeoUrls
     */
    public function __construct(
        RouterInterface $router,
        Manager $manager,
        $baseUrl,
        $curlTimeout,
        $rootDir,
        $cacheScript = 'bin/varnish',
        $invalidateSeoUrls = false
    ) {
        $this->router = $router;
        $this->manager = $manager;
        $this->urlCache = [];
        $this->documentParamCache = [];
        $this->baseUrl = $baseUrl;
        $this->rootDir = $rootDir;
        $this->curlTimeout = $curlTimeout;
        $this->urlCollectors = [];
        $this->cacheScript = $cacheScript;
        $this->invalidateSeoUrls = $invalidateSeoUrls;
    }

    /**
     * Adds URL which should be invalidated.
     *
     * @param string $url
     */
    public function addUrl($url)
    {
        $this->urlCache[$url] = true;
    }

    /**
     * Adds multiple urls.
     *
     * @param array $urls
     */
    public function addUrls($urls)
    {
        foreach ($urls as $url) {
            $this->addUrl($url);
        }
    }

    /**
     * Returns array of links.
     *
     * @return array
     */
    public function getUrls()
    {
        return array_keys($this->urlCache);
    }

    /**
     * Adds document whose links should be invalidated.
     *
     * @param string $field
     * @param string $value
     */
    public function addDocumentParameter($field, $value)
    {
        $this->documentParamCache[md5($value . $field)] = [$field, $value];
    }

    /**
     * Adds multiple documents whose links should be invalidated.
     *
     * @param array $params Add as array($field, $value).
     */
    public function addMultipleDocumentParameters(array $params)
    {
        foreach ($params as $param) {
            $this->addDocumentParameter($param[0], $param[1]);
        }
    }

    /**
     * Returns array of links got by document id.
     *
     * @return array
     */
    protected function getUrlsByDocumentParameter()
    {
        if (count($this->documentParamCache) < 1) {
            return [];
        }

        $urls = [];
        $query = new Query();
        $queryTerms = [];

        foreach ($this->documentParamCache as $param) {
            $queryTerms[$param[0]][] = $param[1];
        }

        foreach ($queryTerms as $field => $values) {
            $termQuery = new TermQuery($field, $values);
            $query->addQuery($termQuery, 'should');
        }

        $limitFilter = new LimitFilter(count($this->documentParamCache));
        $repository = $this->manager->getRepository('MultiModel');
        $search = $repository->createSearch()->addQuery($query)
            ->addFilter($limitFilter);
        $documents = $repository->execute($search);

        // Add all category urls to invalidate.
        foreach ($documents as $document) {
            if (is_array($document->url)) {
                foreach ($document->url as $url) {
                    $urls[] = $url['url'];
                }
            }
        }

        array_walk($urls, [$this, 'addWildcard']);
        $this->addUrls($urls);

        return $urls;
    }

    /**
     * Creates temporary file to store urls.
     *
     * @return string
     */
    public function createUrlsTempFile()
    {
        $hash = md5(microtime(true));
        $links = array_merge($this->getUrls(), $this->getUrlsByDocumentParameter());
        $urlsFile = "/tmp/urls_{$hash}.txt";
        $urls = [];

        foreach ($links as $url) {
            $separator = ($url[0] !== '/') ? '/' : '';
            $urls[] = $this->baseUrl . $separator . $url;
        }
        file_put_contents($urlsFile, implode(PHP_EOL, $urls));

        return $urlsFile;
    }

    /**
     * Invalidates collected URLs.
     *
     * @return string Executed file name
     */
    public function invalidate()
    {
        $script = escapeshellcmd($this->rootDir . "/../{$this->cacheScript}");
        $urlsFile = escapeshellarg($this->createUrlsTempFile());
        $curlTimeout = escapeshellarg($this->curlTimeout);
        // Execute in background.
        $process = new Process(sprintf('%s %s %s', $script, $urlsFile, $curlTimeout));
        $process->start();

        $this->resetCache();

        return $urlsFile;
    }

    /**
     * Resets URLs cache.
     */
    protected function resetCache()
    {
        $this->urlCache = [];
        $this->documentParamCache = [];
    }

    /**
     * Collect all urls for invalidation.
     *
     * @param string            $type
     * @param DocumentInterface $document
     */
    public function loadUrlsFromDocument($type, $document)
    {
        if ($this->invalidateSeoUrls) {
            // Default behavior.

            if (isset($document->url) && is_array($document->url)) {
                foreach ($document->url as $url) {
                    $this->addUrl($url['url']);
                }
            }
        }
        // Special behavior from bundles.
        foreach ($this->urlCollectors as $collector) {
            $this->addUrls($collector->getDocumentUrls($type, $document, $this->router));
            $this->addMultipleDocumentParameters($collector->getDocumentParameters($type, $document));
        }
    }

    /**
     * Urls by type.
     *
     * @param string $type
     */
    public function loadUrlsByType($type)
    {
        foreach ($this->urlCollectors as $collector) {
            $this->addUrls($collector->getUrlsByType($type, $this->router));
        }
    }

    /**
     * Add '*' to end if string endings with '/'.
     *
     * @param string $item
     * @param string $key
     */
    protected function addWildcard(&$item, $key)
    {
        if (substr($item, -1) === '/') {
            $item .= '*';
        }
    }

    /**
     * Adds additional URL collector service.
     *
     * @param DocumentUrlCollectorInterface $collector
     */
    public function addUrlCollector(DocumentUrlCollectorInterface $collector)
    {
        $this->urlCollectors[] = $collector;
    }

    /**
     * Setter for $invalidateSeoUrls.
     *
     * @param bool $invalidateSeoUrls
     */
    public function setInvalidateSeoUrls($invalidateSeoUrls)
    {
        $this->invalidateSeoUrls = $invalidateSeoUrls;
    }
}
