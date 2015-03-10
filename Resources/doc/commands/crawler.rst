Repository crawler
==================

Overview
--------

There may be situations when we would want to iterate over large data set for whatever reason
(Analysis, validation, other). This should be done in async way using celery and ElasticSearch scan api.
ONGR ConnectionsBundle provides solution for that. You can initiate it simply by running command:
`ongr:repository-crawler:crawl [*configured in config.yml*]`. All what you have to do is to configure
source and actions that will be performed on each iteration. You can configure as many actions as you want, but each
will be launched as separate crawler.

You can attach your custom actions on each of five events:

- onSource
    Listeners provide data from the source. Has optional argument `scroll`, that sets Elasticsearch scroll parameter, it
    defaults to `1m`

- onStart
    Listeners are notified that item processing is about to begin, so they can, for example, lock end-database tables
    for.

- onModify
    Assigns data from the source item to the relevant fields in end-item, modifies them as needed.

- onConsume
    Consumes end-item as required, e.g. saves it in a repository.

- onFinish
    Does whatever is expected after processing all source items, e.g. clear cache, commit bulk operations etc.

In example your simplest crawler configuration could be, as presented in this `yml`:

.. code-block:: yaml

    test.crawler.source:
        class: ONGR\ConnectionsBundle\Crawler\Event\RepositoryCrawlerSource
        calls:
            - [ setScrollSetting, [ 5m ] ] #Optional, default value is 1m
        arguments:
           - @es.manager
           - AcmeTestBundle:Product
        tags:
            - { name: kernel.event_listener, event: ongr.pipeline.repository_crawler.myCrawler.source, method: onSource }

    test.crawler.modifier:
        class: ONGR\YourBundle\Crawler\Event\CrawlerModifyEvent
        tags:
            - { name: kernel.event_listener, event: ongr.pipeline.repository_crawler.myCrawler.modify, method: onModify }

..

As you see from example above, event listeners should be named accordingly to pattern:
ongr.pipeline.repository_crawler.<Target action>.<Event> events. Event `ongr.pipeline.repository_crawler.default.modify` -
onModify will trigger target action that is defined in file `Acme\YourBundle\Crawler\Event\CrawlerModifyEvent`.
Contents this file could be similar to:

.. code-block:: php

    namespace Acme\YourBundle\Crawler\Event;

    /**
     * Will be called on each iteration.
     */
    class CrawlerModifyEvent extends AbstractCrawlerModifier
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
            // Implementation should contain body with action,
            // that will be performed on each iteration with each $item, e.g.:
            echo "{$item->getId()} | {$item->getTitle()} \n";
        }
    }

..

And the command that initiates this crawler would be: `ongr:repository-crawler:crawl myCrawler`
