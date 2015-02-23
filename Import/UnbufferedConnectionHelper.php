<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Import;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use PDO;
use LogicException;

/**
 * Helper class for getting unbuffered queries.
 */
class UnbufferedConnectionHelper
{
    /**
     * Closes connection if open, opens a unbuffered connection.
     *
     * @param Connection $connection
     *
     * @throws InvalidArgumentException
     */
    public static function unbufferConnection(Connection $connection)
    {
        /** @var PDOConnection $wrappedConnection */
        $wrappedConnection = $connection->getWrappedConnection();

        if (!$wrappedConnection instanceof PDOConnection) {
            throw new InvalidArgumentException('unbufferConection can only be used with pdo_mysql Doctrine driver.');
        }

        if ($wrappedConnection->getAttribute(PDO::ATTR_DRIVER_NAME) != 'mysql') {
            throw new InvalidArgumentException(
                'unbufferConection can only be used with PDO mysql driver, got "' .
                $wrappedConnection->getAttribute(PDO::ATTR_DRIVER_NAME) . '" instead.'
            );
        }

        if ($connection->isConnected()) {
            $connection->close();
        }

        $connection->getWrappedConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $connection->connect();
    }
}
