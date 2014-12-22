<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\ConnectionsBundle\Import\Item\ImportItem;
use ONGR\ElasticsearchBundle\ORM\Repository;
use Traversable;

/**
 * This class is able to iterate over entities without storing objects in doctrine memory.
 */
class DoctrineImportIterator extends \IteratorIterator
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Traversable            $iterator
     * @param EntityManagerInterface $manager
     * @param Repository             $repository
     */
    public function __construct(Traversable $iterator, EntityManagerInterface $manager, Repository $repository)
    {
        $this->repository = $repository;
        $this->manager = $manager;

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $doctrineEntity = parent::current();

        return new ImportItem($doctrineEntity[0], $this->repository->createDocument());
    }

    /**
     * We need to clear identity map before navigating to next record.
     */
    public function next()
    {
        $this->manager->clear();
        parent::next();
    }
}
