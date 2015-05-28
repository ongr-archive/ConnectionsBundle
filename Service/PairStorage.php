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

use ONGR\ConnectionsBundle\Document\Pair;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\ORM\Repository;

/**
 * Responsible for managing pairs actions.
 */
class PairStorage
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->repository = $this->manager->getRepository('ONGRConnectionsBundle:Pair');
    }

    /**
     * Returns pair value by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $pair = $this->repository->find($key);

        return $pair ? $pair->getValue() : null;
    }

    /**
     * Sets pair value. Returns pair with values.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return DocumentInterface
     */
    public function set($key, $value)
    {
        $pair = $this->repository->find($key);
        if ($pair === null) {
            $pair = new Pair();
            $pair->setId($key);
        }

        $pair->setValue($value);
        $this->save($pair);

        return $pair;
    }

    /**
     * Removes pair by key.
     *
     * @param string $key
     */
    public function remove($key)
    {
        $pair = $this->repository->find($key);
        if ($pair !== null) {
            $this->repository->remove($pair->getId());
            $this->manager->flush();
            $this->manager->refresh();
        }
    }

    /**
     * Saves pair object.
     *
     * @param Pair $pair
     */
    private function save(Pair $pair)
    {
        $this->manager->persist($pair);
        $this->manager->commit();
        $this->manager->refresh();
    }
}
