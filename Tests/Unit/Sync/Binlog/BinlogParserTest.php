<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Sync\Binlog;

use ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser;

/**
 * Unit tests for binary log parser.
 */
class BinlogParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if exception is thrown when an unknown statement is passed.
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Unknown statement of type INVALID STATEMENT
     */
    public function testDetectType()
    {
        $method = new \ReflectionMethod(
            'ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser',
            'detectQueryType'
        );
        $method->setAccessible(true);

        $method->invoke(new BinlogParser('', ''), 'INVALID STATEMENT');
    }

    /**
     * Line parser test.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Errcode: 2 - No such file or directory
     */
    public function testGetNextLineException()
    {
        $method = new \ReflectionMethod(
            'ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser',
            'getNextLine'
        );
        $method->setAccessible(true);

        /** @var BinlogParser|\PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser')
            ->setConstructorArgs(['', ''])
            ->setMethods(['getLine'])
            ->getMock();
        $parser
            ->expects($this->any())
            ->method('getLine')
            ->willReturn("mysqlbinlog: File '/test' not found (Errcode: 2 - No such file or directory)");

        $method->invoke($parser, BinlogParser::LINE_TYPE_ERROR);
    }
}
