<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline;

/**
 * Exception for skipping items.
 *
 * This exception (or exception derived from this)
 * should be thrown inside pipeline modifier
 * to indicate that this item should be skipped.
 * Thrown exception will be available for consumers.
 */
class ItemSkipException extends \Exception
{
}
