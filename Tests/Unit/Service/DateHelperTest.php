<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Service;

use ONGR\ConnectionsBundle\Service\DateHelper;

/**
 * Test for DateHelper.
 */
class DateHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestConvertDateData()
    {
        $out = [];
        // Case #0 format Y-m-d H:i:s.
        $expected = new \DateTime();
        $expected->setTimestamp(strtotime('2013-11-28 10:32:00'));
        $out[] = [
            '2013-11-28 10:32:00',
            $expected,
        ];

        // Case #1 format Y-m-d.
        $expected = new \DateTime();
        $expected->setTimestamp(strtotime('2013-11-27'));
        $out[] = [
            '2013-11-27',
            $expected,
        ];

        // Case #2 format - word.
        $expected = new \DateTime();
        $expected->setTimestamp(strtotime('today'));
        $out[] = [
            'today',
            $expected,
        ];

        // Case #3 empty string.
        $out[] = [
            '',
            null,
        ];

        return $out;
    }

    /**
     * Tests convertDate method.
     *
     * @param string    $date
     * @param \DateTime $expected
     *
     * @dataProvider getTestConvertDateData
     */
    public function testConvertDate($date, $expected)
    {
        $helper = new DateHelper();
        $this->assertEquals($expected, $helper->convertDate($date));
    }
}
