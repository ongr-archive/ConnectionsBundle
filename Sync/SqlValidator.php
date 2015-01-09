<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync;

use InvalidArgumentException;

/**
 * Validator for sql functions.
 */
class SqlValidator
{
    /**
     * Validate table name.
     *
     * @param string $tableName
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function validateTableName($tableName)
    {
        if (!preg_match('|^[a-z_0-9]+$|i', $tableName)) {
            throw new InvalidArgumentException("Invalid table name specified: \"$tableName\"");
        }

        return $tableName;
    }
}
