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
use \DateTime;

/**
 * Sync data provider from MySQL binlog.
 */
class BinlogDiffProvider extends DiffProvider
{
    const LAST_SYNC_DATE_PARAM = 'last_sync_date';
    const LAST_SYNC_POSITION_PARAM = 'last_sync_position';

    /**
     * @var BinlogDecorator
     */
    private $binlogDecorator;

    /**
     * @var \DateTime|int
     */
    private $from;

    /**
     * @var int
     */
    private $startType;

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
     * @return \DateTime|int
     *
     * @throws \InvalidArgumentException
     */
    public function getFrom()
    {
        if ($this->from === null) {
            if ($this->getStartType() == BinlogParser::START_TYPE_DATE) {
                $temp_date = $this->getPairStorage()->get(self::LAST_SYNC_DATE_PARAM);

                if ($temp_date === null) {
                    $this->generateLastSyncNotSetError(self::LAST_SYNC_DATE_PARAM);
                } else {
                    $this->from = new DateTime($temp_date);
                }
            } elseif ($this->getStartType() == BinlogParser::START_TYPE_POSITION) {
                $this->from = $this->getPairStorage()->get(self::LAST_SYNC_POSITION_PARAM);

                if ($this->from === null) {
                    $this->generateLastSyncNotSetError(self::LAST_SYNC_POSITION_PARAM);
                }
            }
        }

        return $this->from;
    }

    /**
     * @param \DateTime|int $from
     */
    public function setFrom($from)
    {
        if ($this->getStartType() == BinlogParser::START_TYPE_DATE) {
            $this->getPairStorage()->set(self::LAST_SYNC_DATE_PARAM, $from);
        } elseif ($this->getStartType() == BinlogParser::START_TYPE_POSITION) {
            $this->getPairStorage()->set(self::LAST_SYNC_POSITION_PARAM, $from);
        }

        $this->from = $from;
    }

    /**
     * @return int
     *
     * @throws \LogicException
     */
    public function getStartType()
    {
        if ($this->startType === null) {
            throw new \LogicException('setStartType must be called before getStartType.');
        }

        return $this->startType;
    }

    /**
     * @param int $startType
     */
    public function setStartType($startType)
    {
        $this->startType = $startType;
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
                $this->getFrom(),
                $this->getStartType(),
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
        if ($this->valid() !== false) {
            if ($this->getStartType() == BinlogParser::START_TYPE_DATE) {
                $this->setFrom($this->current()->getTimestamp());
            } elseif ($this->getStartType() == BinlogParser::START_TYPE_POSITION) {
                $this->setFrom($this->current()->getDiffId());
            }
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

    /**
     * Generates user friendly error message.
     *
     * @param string $parameter
     *
     * @throws \InvalidArgumentException
     */
    private function generateLastSyncNotSetError($parameter)
    {
        throw new \InvalidArgumentException(
            'Last sync parameter is not set! ' .
            'To set it, use command: ' .
            'ongr:sync:provide:parameter --set="<new value>" ' . $parameter
        );
    }
}
