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
 * DiffItem of "Update" type. Includes old version of an item.
 */
class UpdateDiffItem extends BaseDiffItem
{
    /**
     * @var mixed
     */
    private $oldItem;

    /**
     * @return mixed
     */
    public function getOldItem()
    {
        return $this->oldItem;
    }

    /**
     * @param mixed $oldItem
     */
    public function setOldItem($oldItem)
    {
        $this->oldItem = $oldItem;
    }

    /**
     * {@inheritdoc}
     */
    public function setWhereParams($params)
    {
        $this->setOldItem($params);
    }

    /**
     * {@inheritdoc}
     */
    public function setSetParams($params)
    {
        $this->setItem($params);
    }
}
