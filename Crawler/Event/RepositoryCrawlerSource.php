<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\ConnectionsBundle\Crawler\Event;

use ONGR\ConnectionsBundle\EventListener\AbstractCrawlerSource;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ElasticsearchBundle\DSL\Query\MatchAllQuery;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\ORM\Repository;

/**
 * Provides data from Elasticsearch repository.
 */
class RepositoryCrawlerSource extends AbstractCrawlerSource
{
    /**
     * @var Repository Elasticsearch repository.
     */
    protected $repository;

    /**
     * @var string
     */
    protected $scrollSetting = '1m';

    /**
     * Constructor.
     *
     * @param Manager $manager
     * @param string  $repositoryName
     */
    public function __construct(Manager $manager, $repositoryName)
    {
        $this->repository = $manager->getRepository($repositoryName);
    }

    /**
     * Source provider event.
     *
     * @param SourcePipelineEvent $sourceEvent
     */
    public function onSource(SourcePipelineEvent $sourceEvent)
    {
        $sourceEvent->addSource($this->getAllDocuments());
    }

    /**
     * Gets all documents by given type.
     *
     * @return DocumentIterator
     */
    public function getAllDocuments()
    {
        $matchAllQuery = new MatchAllQuery();
        $search = $this->repository
            ->createSearch()
            ->addQuery($matchAllQuery)
            ->setScroll($this->getScrollSetting())
            ->setSearchType('scan');
        $documents = $this->repository->execute($search);

        return $documents;
    }

    /**
     * @return string
     */
    private function getScrollSetting()
    {
        return $this->scrollSetting;
    }

    /**
     * @param string $scrollSetting
     */
    public function setScrollSetting($scrollSetting)
    {
        $this->scrollSetting = $scrollSetting;
    }
}
