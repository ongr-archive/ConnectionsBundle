<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Fixtures\Sync\Extractor;

use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\AbstractDiffItem;

/**
 * InvalidDiffItem class.
 */
class InvalidDiffItem extends AbstractDiffItem
{
    /**
     * Sets Item values by WHERE params.
     *
     * @param mixed $params
     *
     * @return mixed
     */
    public function setWhereParams($params)
    {
    }

    /**
     * Sets Item values by SET params.
     *
     * @param mixed $params
     *
     * @return mixed
     */
    public function setSetParams($params)
    {
    }
}
