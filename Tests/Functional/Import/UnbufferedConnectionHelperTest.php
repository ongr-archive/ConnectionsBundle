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
     *
     * @runInSeparateProcess
     */
    public function testUnbufferedConnectionHelper()
    {
        $this->importData('Import/stress.sql');

        UnbufferedConnectionHelper::unbufferConnection($this->getConnection());
        $this->getConnection()->close();

        $bufferredTimeStart = microtime();
        $statementBuff = new Statement('SELECT * FROM generator_64k', $this->getConnection());
        $statementBuff->execute();
        $bufferredTime = microtime() - $bufferredTimeStart;
        $this->getConnection()->close();

        UnbufferedConnectionHelper::unbufferConnection($this->getConnection());
        $unBufferredTimeStart = microtime();
        $statement = new Statement('SELECT * FROM generator_64k', $this->getConnection());
        $statement->execute();
        $unBufferedTime = microtime() - $unBufferredTimeStart;
        $this->getConnection()->close();

        $this->assertTrue(
            $unBufferedTime < $bufferredTime,
            'Unbuffered query should return faster after execute().'
        );
    }
}
