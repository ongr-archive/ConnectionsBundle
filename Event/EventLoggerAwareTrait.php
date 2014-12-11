<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * Simplifies logger usage.
 */
trait EventLoggerAwareTrait
{
    use LoggerAwareTrait;

    /**
     * Logs message if logger is set.
     *
     * @param string $message
     * @param string $level
     * @param array  $context
     */
    private function log($message, $level = LogLevel::DEBUG, $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
