<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\Item;

/**
 * Contains information about update operation, including item, timestamp, etc.
 */
abstract class AbstractDiffItem
{
    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var string
     */
    private $category;

    /**
     * @var mixed
     */
    private $item;

    /**
     * @var int|null
     */
    private $itemId;

    /**
     * @var int|null
     */
    private $diffId;

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * Returns item ID. Setting item ID is *optional* therefore method may return null.
     *
     * @return int|null
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int|null
     */
    public function getDiffId()
    {
        return $this->diffId;
    }

    /**
     * @param int|null $diffId
     */
    public function setDiffId($diffId)
    {
        $this->diffId = $diffId;
    }

    /**
     * Sets Item values by WHERE params.
     *
     * @param mixed $params
     *
     * @return mixed
     */
    abstract public function setWhereParams($params);

    /**
     * Sets Item values by SET params.
     *
     * @param mixed $params
     *
     * @return mixed
     */
    abstract public function setSetParams($params);
}
