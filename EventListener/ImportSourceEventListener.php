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

use ONGR\ConnectionsBundle\Import\DoctrineImportIterator;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;

/**
 * Class ImportSourceEventListener - gets items from Doctrine, creates empty Elasticsearch documents.
 */
class ImportSourceEventListener extends AbstractImportSourceEventListener
{
    /**
     * Gets all documents by given type.
     *
     * @return DoctrineImportIterator
     */
    public function getAllDocuments()
    {
        return new DoctrineImportIterator(
            $this->getDoctrineManager()->createQuery("SELECT e FROM {$this->getentityClass()} e")->iterate(),
            $this->getDoctrineManager(),
            $this->getElasticsearchManager()->getRepository($this->getDocumentClass())
        );
    }

    /**
     * Gets data and adds source.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource($this->getAllDocuments());
    }
}
