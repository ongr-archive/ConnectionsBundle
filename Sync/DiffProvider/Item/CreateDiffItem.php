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
 * DiffItem of "Create" type.
 */
class CreateDiffItem extends BaseDiffItem
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setWhereParams($params)
    {
        // Do nothing.
    }

    /**
     * {@inheritdoc}
     */
    public function setSetParams($params)
    {
        $this->setItem($params);
    }
}
