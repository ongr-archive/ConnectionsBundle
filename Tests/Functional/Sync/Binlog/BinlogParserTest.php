<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync\Binlog;

use ONGR\ConnectionsBundle\Entity\SyncJob;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Functional test for binary log parser.
 */
class BinlogParserTest extends TestBase
{
    /**
     * Clear logs before each test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->getConnection()->executeQuery('RESET MASTER');
    }

    /**
     * Data provider for testParse().
     *
     * @return array
     */
    public function getTestParseData()
    {
        // Case #0: without from.
        $expectedItems = [
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:32:58'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_CREATE,
                    'table' => 'test',
                    'set' => [
                        1 => '1',
                        2 => 'Hello world!',
                    ],
                ],
            ],
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:34:06'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_CREATE,
                    'table' => 'test',
                    'set' => [
                        1 => '2',
                        2 => 'Hello world!',
                    ],
                ],
            ],
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:34:39'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_UPDATE,
                    'table' => 'test',
                    'where' => [
                        1 => '2',
                        2 => 'Hello world!',
                    ],
                    'set' => [
                        1 => '2',
                        2 => 'Updated with where',
                    ],
                ],
            ],
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:35:22'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_UPDATE,
                    'table' => 'test',
                    'where' => [
                        1 => '1',
                        2 => 'Hello world!',
                    ],
                    'set' => [
                        1 => '1',
                        2 => 'Updated without where',
                    ],
                ],
            ],
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:35:22'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_UPDATE,
                    'table' => 'test',
                    'where' => [
                        1 => '2',
                        2 => 'Updated with where',
                    ],
                    'set' => [
                        1 => '2',
                        2 => 'Updated without where',
                    ],
                ],
            ],
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:35:46'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_DELETE,
                    'table' => 'test',
                    'where' => [
                        1 => '1',
                        2 => 'Updated without where',
                    ],
                ],
            ],
            [
                BinlogParser::PARAM_DATE => new \DateTime('2014-09-05 10:35:52'),
                BinlogParser::PARAM_QUERY => [
                    'type' => SyncJob::TYPE_DELETE,
                    'table' => 'test',
                    'where' => [
                        1 => '2',
                        2 => 'Updated without where',
                    ],
                ],
            ],
        ];

        $out[] = [$expectedItems, null];

        // Case #1: with from.
        $from = new \DateTime('2014-09-05 10:35:22');
        $expectedItems2 = [$expectedItems[3], $expectedItems[4], $expectedItems[5], $expectedItems[6]];
        $out[] = [$expectedItems2, $from];

        return $out;
    }

    /**
     * Check if items are parsed correctly.
     *
     * @param mixed     $expectedItems
     * @param \DateTime $date
     *
     * @dataProvider getTestParseData()
     */
    public function testParse($expectedItems, $date)
    {
        $parser = new BinlogParser(__DIR__ . '/../../Fixtures/BinlogTest', 'test', $date);

        $this->assertEquals($expectedItems, iterator_to_array($parser));
        // Go two times to check if rewind works as expected.
        $this->assertEquals($expectedItems, iterator_to_array($parser));
    }

    /**
     * Check if exception is thrown when invalid file is passed.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ERROR: File is not a binary log file.
     */
    public function testBinlogFailure()
    {
        $parser = new BinlogParser(__DIR__ . '/../../Fixtures/BinlogTest', 'invalid-file');
        iterator_to_array($parser);
    }

    /**
     * Check if exception is thrown when binlog file not found.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Errcode: 2
     */
    public function testBinlogFileNotFound()
    {
        $parser = new BinlogParser(__DIR__ . '/../../Fixtures/BinlogTest', 'non-existing-file');
        iterator_to_array($parser);
    }

    /**
     * Check if exception is thrown when invalid set statement is passed.
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Expected a statement, got INVALID LINE 1
     */
    public function testStatementFailure()
    {
        $handle = fopen(__DIR__ . '/../../Fixtures/BinlogTest/invalid-file.00002', 'r');

        /** @var BinlogParser|MockObject $parser */
        $parser = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser')
            ->setConstructorArgs(['/../../Fixtures/BinlogTest', 'invalid-file'])
            ->setMethods(['getPipe', 'getLineType'])
            ->getMock();

        $parser
            ->expects($this->any())
            ->method('getPipe')
            ->willReturn($handle);

        $parser
            ->expects($this->any())
            ->method('getLineType')
            ->willReturn(BinlogParser::LINE_TYPE_QUERY);

        iterator_to_array($parser);
    }

    /**
     * Check if exception is thrown when invalid set statement is passed.
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Expected a SET statement, got INVALID LINE 2
     */
    public function testSetFailure()
    {
        $handle = fopen(__DIR__ . '/../../Fixtures/BinlogTest/invalid-file.00002', 'r');

        /** @var BinlogParser|MockObject $parser */
        $parser = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser')
            ->setConstructorArgs(['/../../Fixtures/BinlogTest', 'invalid-file'])
            ->setMethods(['getPipe', 'handleStart', 'getLineType'])
            ->getMock();

        $parser
            ->expects($this->any())
            ->method('getPipe')
            ->willReturn($handle);

        $parser
            ->expects($this->once())
            ->method('handleStart')
            ->willReturn(['type' => SyncJob::TYPE_CREATE]);

        $parser
            ->expects($this->any())
            ->method('getLineType')
            ->willReturn(BinlogParser::LINE_TYPE_QUERY);

        iterator_to_array($parser);
    }

    /**
     * Check if exception is thrown when invalid where statement is passed.
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Expected a WHERE statement, got INVALID LINE 2
     */
    public function testWhereFailure()
    {
        $handle = fopen(__DIR__ . '/../../Fixtures/BinlogTest/invalid-file.00002', 'r');

        /** @var BinlogParser|MockObject $parser */
        $parser = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser')
            ->setConstructorArgs(['/../../Fixtures/BinlogTest', 'invalid-where'])
            ->setMethods(['getPipe', 'handleStart', 'getLineType'])
            ->getMock();

        $parser
            ->expects($this->any())
            ->method('getPipe')
            ->willReturn($handle);

        $parser
            ->expects($this->once())
            ->method('handleStart')
            ->willReturn(['type' => SyncJob::TYPE_DELETE]);

        $parser
            ->expects($this->any())
            ->method('getLineType')
            ->willReturn(BinlogParser::LINE_TYPE_QUERY);

        iterator_to_array($parser);
    }

    /**
     * Test custom pipe.
     */
    public function testStatements()
    {
        $handle = fopen(__DIR__ . '/../../Fixtures/BinlogTest/raw-file', 'r');

        /** @var BinlogParser|MockObject $parser */
        $parser = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser')
            ->setConstructorArgs(['/../../Fixtures/BinlogTest', 'raw-file'])
            ->setMethods(['getPipe'])
            ->getMock();

        $parser
            ->expects($this->any())
            ->method('getPipe')
            ->willReturn($handle);

        iterator_to_array($parser);
    }
}
