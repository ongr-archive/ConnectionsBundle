<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog;

use Doctrine\DBAL\Connection;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\DiffProvider\DiffProvider;
use ONGR\ConnectionsBundle\Service\PairStorage;
use InvalidArgumentException;

/**
 * Sync data provider from MySQL binlog.
 */
class BinlogDiffProvider extends DiffProvider
{
    /**
     * @var BinlogDecorator
     */
    private $binlogDecorator;

    /**
     * @var \DateTime
     */
    private $fromDate;

    /**
     * @var PairStorage
     */
    private $pairStorage;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $baseName;

    /**
     * @var string
     */
    private $connectionName = 'default';

    /**
     * @return string
     *
     * @throws \LogicException
     */
    public function getBaseName()
    {
        if ($this->baseName === null) {
            throw new \LogicException('setBaseName must be called before getBaseName.');
        }

        return $this->baseName;
    }

    /**
     * @param string $baseName
     */
    public function setBaseName($baseName)
    {
        $this->baseName = $baseName;
    }

    /**
     * @return Connection
     *
     * @throws \LogicException
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            throw new \LogicException('setConnection must be called before getConnection.');
        }

        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     *
     * @throws \LogicException
     */
    public function getConnectionName()
    {
        if ($this->connectionName === null) {
            throw new \LogicException('setConnectionName must be called before getConnectionName.');
        }

        return $this->connectionName;
    }

    /**
     * @param string $connectionName
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * @return string
     *
     * @throws \LogicException
     */
    public function getDir()
    {
        if ($this->connectionName === null) {
            throw new \LogicException('setDir must be called before getDir.');
        }

        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Gets pair storage.
     *
     * @return PairStorage
     *
     * @throws \LogicException
     */
    public function getPairStorage()
    {
        if ($this->pairStorage === null) {
            throw new \LogicException('setPairStorage must be called before getPairStorage.');
        }

        return $this->pairStorage;
    }

    /**
     * Sets pair storage.
     *
     * @param PairStorage $pairStorage
     */
    public function setPairStorage(PairStorage $pairStorage)
    {
        $this->pairStorage = $pairStorage;
    }

    /**
     * @return \DateTime
     *
     * @throws \InvalidArgumentException
     */
    public function getFromDate()
    {
        if ($this->fromDate === null) {
            $this->fromDate = $this->getPairStorage()->get('last_sync_date');
        }

        if ($this->fromDate == null) {
            throw new \InvalidArgumentException('Last sync date is not set!');
        }

        return $this->fromDate;
    }

    /**
     * @param \DateTime $fromDate
     */
    public function setFromDate($fromDate)
    {
        $this->getPairStorage()->set('last_sync_date', $fromDate);
        $this->fromDate = $fromDate;
    }

    /**
     * @return BinlogDecorator
     */
    public function getBinlogDecorator()
    {
        if ($this->binlogDecorator === null) {
            $this->binlogDecorator = new BinlogDecorator(
                $this->getConnection(),
                $this->getDir(),
                $this->getBaseName(),
                $this->getFromDate(),
                $this->getConnectionName()
            );
        }

        return $this->binlogDecorator;
    }

    /**
     * {@inheritdoc}
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $this->context = $event->getContext();
        $event->addSource($this);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->getBinlogDecorator()->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if ($this->valid() == true) {
            $this->current()->getTimestamp();
        }
        $this->getBinlogDecorator()->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->getBinlogDecorator()->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->getBinlogDecorator()->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->getBinlogDecorator()->rewind();
    }
}
