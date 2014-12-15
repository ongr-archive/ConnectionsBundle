<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\EventListener;

use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * AbstractImportConsumeEventListener -  called after modify event. Puts document into Elasticsearch.
 */
abstract class AbstractImportConsumeEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $importItemClass;

    /**
     * @var DocumentInterface
     */
    protected $document;

    /**
     * @param Manager $manager
     * @param string  $itemClass
     */
    public function __construct(Manager $manager, $itemClass)
    {
        $this->manager = $manager;
        $this->importItemClass = $itemClass;
    }

    /**
     * Consume event.
     *
     * @param ItemPipelineEvent $event
     *
     * @return bool
     */
    public function onConsume(ItemPipelineEvent $event)
    {
        if (!$this->validateItem($event)) {
            return false;
        }

        $this->log(
            sprintf(
                'Start update single document of type %s id: %s',
                get_class($this->document),
                $this->document->getId()
            )
        );

        if (!$this->persistDocument()) {
            return false;
        };

        $this->log('End an update of a single document.');

        return true;
    }

    /**
     * Persist document to Elasticsearch.
     *
     * @return bool
     */
    protected function persistDocument()
    {
        $this->manager->persist($this->document);

        return true;
    }

    /**
     * Validates data of event item and prepares gets document.
     *
     * @param ItemPipelineEvent $event
     *
     * @return bool
     */
    protected function validateItem(ItemPipelineEvent $event)
    {
        $item = $event->getItem();

        if (!$item instanceof $this->importItemClass) {
            $this->log("Item provided is not an {$this->importItemClass}", LogLevel::NOTICE);

            return false;
        }

        /** @var DocumentInterface $document */
        $this->document = $event->getItem()->getDocument();

        if ($this->document->getId() === null) {
            $this->log('No document id found. Update skipped.', LogLevel::NOTICE);

            $this->document = null;

            return false;
        }

        return true;
    }
}
