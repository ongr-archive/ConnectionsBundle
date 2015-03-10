<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Import;

use InvalidArgumentException;
use ONGR\ConnectionsBundle\Import\UnbufferedConnectionHelper;
use PDO;

class UnbufferedConnectionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instructs PHPUnit to run this test in a separate process.
     *
     * Since this test disables PDOConnection autoloading, it makes other tests fail and therefore
     * should be contained.
     *
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * Test UnbufferedConnectionHelper with bad driver.
     *
     * @runInSeparateProcess
     * ç
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage unbufferConection can only be used with pdo_mysql Doctrine driver.
     */
    public function testUnbufferedConnectionHelperBadDriver()
    {
        UnbufferedConnectionHelper::unbufferConnection($this->getDbalConnection());
    }

    /**
     * Test UnbufferedConnectionHelper with bad wrapped driver.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage unbufferConection can only be used with PDO mysql driver, got "bad_driver" instead.
     */
    public function testUnbufferedConnectionHelperBadWrappedConnection()
    {
        $mockWrappedConnection = $this->getMockPDOConnection();
        $mockWrappedConnection
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('bad_driver');

        $mockConnection = $this->getDbalConnection();
        $mockConnection->expects($this->once())->method('getWrappedConnection')->willReturn($mockWrappedConnection);

        UnbufferedConnectionHelper::unbufferConnection($mockConnection);
    }

    /**
     * Test UnbufferedConnectionHelper with good driver.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUnbufferedConnectionHelper()
    {
        $mockWrappedConnection = $this->getMockPDOConnection();
        $mockWrappedConnection
            ->expects($this->once())
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('mysql');

        $mockWrappedConnection
            ->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);

        $mockConnection = $this->getDbalConnection();
        $mockConnection->expects($this->exactly(2))->method('getWrappedConnection')->willReturn($mockWrappedConnection);
        $mockConnection->expects($this->once())->method('isConnected')->willReturn(false);
        $mockConnection->expects($this->once())->method('connect')->willReturn(false);

        UnbufferedConnectionHelper::unbufferConnection($mockConnection);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockPDOConnection()
    {
        $mockWrappedConnection = $this->getMock(
            'Doctrine\DBAL\Driver\PDOConnection',
            ['getAttribute', 'setAttribute', 'exec'],
            [],
            'PDOConnection',
            false,
            false,
            false,
            false
        );

        return $mockWrappedConnection;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    private function getDbalConnection()
    {
        return $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
