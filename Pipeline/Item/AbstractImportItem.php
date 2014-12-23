<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline\Item;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * Import event item carrying both Doctrine entity and ES document.
 */
abstract class AbstractImportItem
{
    /**
     * @var mixed Entity.
     */
    protected $entity;

    /**
     * @var DocumentInterface Document.
     */
    protected $document;

    /**
     * @param mixed             $entity
     * @param DocumentInterface $document
     */
    public function __construct($entity, DocumentInterface $document)
    {
        $this->setEntity($entity);
        $this->setDocument($document);
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $document
     */
    public function setDocument(DocumentInterface $document)
    {
        $this->document = $document;
    }
}
