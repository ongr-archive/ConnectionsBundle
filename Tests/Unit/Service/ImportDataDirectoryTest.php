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

use ONGR\ConnectionsBundle\Service\ImportDataDirectory;

/**
 * Test for ImportDataDirectory.
 */
class ImportDataDirectoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Function test.
     */
    public function testGetDataDirPath()
    {
        $dir = new ImportDataDirectory('/var/www/whatever/app', 'data');
        $this->assertEquals('/var/www/whatever/app/data', $dir->getDataDirPath());
    }
}
