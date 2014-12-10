<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * Import event item carrying both Doctrine element and ES element.
 */
class ImportItem
{
    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var DocumentInterface
     */
    protected $document;

    /**
     * @param mixed             $doctrineItem
     * @param DocumentInterface $elasticItem
     */
    public function __construct($doctrineItem, DocumentInterface $elasticItem)
    {
        $this->setEntity($doctrineItem);
        $this->setDocument($elasticItem);
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $doctrineItem
     */
    public function setEntity($doctrineItem)
    {
        $this->entity = $doctrineItem;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $elasticItem
     */
    public function setDocument(DocumentInterface $elasticItem)
    {
        $this->document = $elasticItem;
    }
}
