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
     * @param Connection $connection
     * @param string     $dir
     * @param string     $baseName
     * @param string     $connectionName
     */
    public function __construct($connection, $dir, $baseName, $connectionName = 'default')
    {
        $from = $this->getFromDate();
        $this->binlogDecorator = new BinlogDecorator($connection, $dir, $baseName, $from, $connectionName);
    }

    /**
     * @return \DateTime
     */
    public function getFromDate()
    {
        if (empty($this->fromDate)) {
            return new \DateTime();
        }

        return $this->fromDate;
    }

    /**
     * @param \DateTime $fromDate
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;
    }

    /**
     * {@inheritdoc}
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource($this);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->binlogDecorator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->binlogDecorator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->binlogDecorator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->binlogDecorator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->binlogDecorator->rewind();
    }
}
