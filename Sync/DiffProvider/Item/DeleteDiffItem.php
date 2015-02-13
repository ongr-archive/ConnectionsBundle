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
 * DiffItem of "Delete" type.
 */
class DeleteDiffItem extends AbstractDiffItem
{
    /**
     * {@inheritdoc}
     */
    public function setWhereParams($params)
    {
        $this->setItem($params);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setSetParams($params)
    {
        // Do nothing.
    }
}
