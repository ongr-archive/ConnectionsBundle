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

use ONGR\ConnectionsBundle\Sync\ActionTypes;

/**
 * DiffItem factory.
 */
class DiffItemFactory
{
    /**
     * Creates DiffItem by type.
     *
     * @param string $type
     *
     * @return CreateDiffItem|DeleteDiffItem|UpdateDiffItem
     * @throws \InvalidArgumentException
     */
    public static function create($type)
    {
        switch ($type) {
            case ActionTypes::CREATE:
                return new CreateDiffItem();
            case ActionTypes::UPDATE:
                return new UpdateDiffItem();
            case ActionTypes::DELETE:
                return new DeleteDiffItem();
            default:
                throw new \InvalidArgumentException("Invalid type {$type}");
        }
    }
}
