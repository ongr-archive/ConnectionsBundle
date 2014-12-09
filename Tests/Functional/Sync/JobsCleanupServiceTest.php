<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync;

use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\JobsCleanupService;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use Psr\Log\LoggerInterface;

/**
 * Functional test for JobsCleanupService.
 */
class JobsCleanupServiceTest extends TestBase
{
    /**
     * Logger mock to test if it gets logged correctly.
     *
     * @param string $size
     *
     * @return LoggerInterface
     */
    public function getLoggerMock($size)
    {
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $loggerMock->expects($this->exactly(1))->method('info')->with('Number of rows deleted: ' . $size);

        return $loggerMock;
    }

    /**
     * Data provider for testDoCleanup().
     *
     * @return array
     */
    public function doCleanupData()
    {
        // Case #0 single shop, no table name set, default table value should be used.
        $out[] = [3, 1, 'TestCase0.sql'];

        // Case #1 multiple shops.
        $shops = ['shop1', 'shop2', 'shop3'];
        $out[] = [5, 2, 'TestCase1.sql', $shops];

        return $out;
    }

    /**
     * Tests doCleanUp.
     *
     * @param int    $countBefore
     * @param int    $countAfter
     * @param string $file
     * @param array  $shops
     *
     * @dataProvider doCleanupData()
     */
    public function testDoCleanup($countBefore, $countAfter, $file, array $shops = [])
    {
        $connection = $this->getConnection();
        $this->importData('JobsCleanupServiceTest/' . $file);
        $count = $connection->fetchAssoc('SELECT COUNT(*) AS `COUNT` FROM `ongr_sync_jobs`')['COUNT'];
        $this->assertEquals($countBefore, $count);
        $service = new JobsCleanupService($connection, 'ongr_sync_jobs', $shops);
        $service->setLogger($this->getLoggerMock($countBefore - $countAfter));
        $service->doCleanup();
        $count = $connection->fetchAssoc('SELECT COUNT(*) AS `COUNT` FROM `ongr_sync_jobs`')['COUNT'];
        $this->assertEquals($countAfter, $count);
    }
}
