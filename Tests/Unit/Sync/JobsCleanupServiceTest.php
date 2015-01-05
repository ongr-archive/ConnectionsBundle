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

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use ONGR\ConnectionsBundle\Entity\SyncJob;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\JobsCleanupService;

/**
 * Unit test for JobsCleanupService.
 */
class JobsCleanupServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    protected function getConnectionMock()
    {
        $connection = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        return $connection;
    }

    /**
     * Data provider for testDoCleanup().
     *
     * @return array
     */
    public function doCleanupData()
    {
        // Case #0 single shop, no table name set, default table value should be used.
        $expectedQuery = 'DELETE FROM ongr_sync_jobs WHERE `status` = ' . SyncJob::STATUS_DONE;
        $out[] = [$expectedQuery];

        // Case #1 single shop, table name set.
        $expectedQuery = 'DELETE FROM test_table WHERE `status` = ' . SyncJob::STATUS_DONE;
        $out[] = [$expectedQuery, 'test_table'];

        // Case #2 multiple shops.
        $shops = ['shop1', 'shop2', 'shop3'];
        $expectedQuery = 'DELETE FROM test_table WHERE' .
            ' `status_shop1` = ' . SyncJob::STATUS_DONE .
            ' AND `status_shop2` = ' . SyncJob::STATUS_DONE .
            ' AND `status_shop3` = ' . SyncJob::STATUS_DONE;
        $out[] = [$expectedQuery, 'test_table', $shops];

        // Case #3 single multiple shop.
        $shops = ['shop1'];
        $expectedQuery = 'DELETE FROM test_table WHERE `status_shop1` = ' . SyncJob::STATUS_DONE;
        $out[] = [$expectedQuery, 'test_table', $shops];

        return $out;
    }

    /**
     * Tests doCleanUp.
     *
     * @param string $expectedQuery
     * @param string $tableName
     * @param array  $shops
     *
     * @dataProvider doCleanupData()
     */
    public function testDoCleanup($expectedQuery, $tableName = '', array $shops = [])
    {
        $connection = $this->getConnectionMock();

        $connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->equalTo($expectedQuery))
            ->will($this->returnValue(true));

        if (empty($tableName)) {
            $service = new JobsCleanupService($connection);
        } else {
            $service = new JobsCleanupService($connection, $tableName, $shops);
        }
        $service->doCleanup();
    }

    /**
     * Tests invalid table name.
     *
     * @param string $tableName
     *
     * @expectedException InvalidArgumentException
     *
     * @dataProvider getTableNameData()
     */
    public function testInvalidTableName($tableName)
    {
        $service = new JobsCleanupService(
            $this->getConnectionMock(),
            $tableName,
            ['']
        );

        $service->doCleanup();
    }

    /**
     * Data provider for testInvalidTableName().
     *
     * @return array
     */
    public function getTableNameData()
    {
        // Case #0 .
        $out[] = ['`test_table`'];

        // Case #1 .
        $out[] = ['SELECT FROM test_table WHERE 1'];

        // Case #2 .
        $out[] = ['; DELETE FROM test_table WHERE 1'];

        return $out;
    }
}
