<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Service;

/**
 * Converts string to \DateTime object.
 */
class DateHelper
{
    /**
     * Converts date string to \DateTime object.
     *
     * @param string $date
     *
     * @return \DateTime|null
     */
    public function convertDate($date)
    {
        if (empty($date)) {
            return null;
        }

        return new \DateTime($date);
    }
}
