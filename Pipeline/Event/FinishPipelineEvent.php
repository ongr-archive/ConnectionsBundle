<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event used by Pipeline to notify that all items was processed.
 */
class FinishPipelineEvent extends Event
{
    use ContextAwareTrait;
}
