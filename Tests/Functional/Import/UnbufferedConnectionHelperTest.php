<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Import;

use Doctrine\DBAL\Statement;
use ONGR\ConnectionsBundle\Import\UnbufferedConnectionHelper;
use ONGR\ConnectionsBundle\Tests\Functional\ESDoctrineTestCase;

class UnbufferedConnectionHelperTest extends ESDoctrineTestCase
{
    /**
     * Tests whether UnbufferedConnectionHelper works as intended in normal circumstances.
     */
    public function testUnbufferedConnectionHelper()
    {
        $this->importData('Import/stress.sql');

        UnbufferedConnectionHelper::unbufferConnection($this->getConnection());
        $statement = new Statement('SELECT * FROM generator_64k', $this->getConnection());
        $statement->execute();
        $memoryUsageUnbuffered = memory_get_usage();
        $this->getConnection()->close();

        $statementBuff = new Statement('SELECT * FROM generator_64k', $this->getConnection());
        $statementBuff->execute();
        $memoryUsageBuffered = memory_get_usage();

        $this->assertTrue(
            $memoryUsageBuffered > $memoryUsageUnbuffered,
            'Unbuffered query should produce lower memory usage.'
        );
    }
}
