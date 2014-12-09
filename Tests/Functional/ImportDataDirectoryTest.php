<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional;

use ONGR\ConnectionsBundle\Service\DateHelper;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Functional tests for ImportDataDirectory.
 */
class ImportDataDirectoryTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->tearDown();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $system = new Filesystem();

        $system->remove($this->getService()->getDataDirPath());

        $system->remove(sys_get_temp_dir() . '/ongr');
    }

    /**
     * Test if is able to create directory.
     */
    public function testCreate()
    {
        $files = new Filesystem();

        $this->assertFalse($files->exists($this->getService()->getDataDirPath()));

        $this->getService()->create();

        $this->assertTrue($files->exists($this->getService()->getDataDirPath()));

        // Nothing wrong should happen on second time.
        $this->getService()->create();
    }

    /**
     * Get Current Dir functional test.
     *
     * @param string $expectedPath
     * @param array  $provider
     * @param bool   $unique
     *
     * @dataProvider getTestGetCurrentDirData
     */
    public function testGetCurrentDir($expectedPath, $provider, $unique)
    {
        $files = new Filesystem();

        $pathPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ongr' . DIRECTORY_SEPARATOR;

        $this->assertFalse(
            $files->exists($pathPrefix . $expectedPath),
            "Asserting, that expected path doesn't exists yet"
        );

        $dir = new ImportDataDirectory(sys_get_temp_dir(), 'ongr');

        $actualPath = $dir->getCurrentDir($provider, $unique);

        $this->assertTrue($files->exists($pathPrefix . $actualPath), 'Asserting, that expected path created');

        $this->assertGreaterThanOrEqual($expectedPath, $actualPath);
    }

    /**
     * @return array
     */
    public function getTestGetCurrentDirData()
    {
        $provider = 'testProvider';
        $datePath = date('Y/m/d');

        // Case #0: valid provider, with unique.
        $unique = date('H_i_');
        $ret[] = [
            $datePath . '/' . $provider . '/' . $unique,
            $provider,
            true,
        ];

        // Case #1: valid provider, without unique.
        $ret[] = [
            $datePath . '/' . $provider,
            $provider,
            false,
        ];

        // Case #2: valid provider, no optional parameter.
        $ret[] = [
            $datePath . '/' . $provider,
            $provider,
            null,
        ];

        return $ret;
    }

    /**
     * @return array
     */
    public function getTestCleanupBeforeData()
    {
        $allFiles = [
            '2012/11/10/foo/1.txt',
            '2012/11/10/bar/2.txt',
            '2012/11/10/baz/20_20_20_hash_uniqid/3.txt',
            '2013/01/10/baz/4.txt',
            '2013/04/10/baz/5.txt',
            '2013/08/10/baz/6.txt',
            '2013/11/27/baz/7.txt',
            '2019/01/10/baz/8.txt',
        ];

        $out = [];
        // Case #0.
        $out[] = [
            // Today ago all providers.
            new \DateTime('2013-11-27 00:00:00'),
            $allFiles,
            [
                '2012/11/10/foo/1.txt',
                '2012/11/10/bar/2.txt',
                '2012/11/10/baz/20_20_20_hash_uniqid/3.txt',
                '2013/01/10/baz/4.txt',
                '2013/04/10/baz/5.txt',
                '2013/08/10/baz/6.txt',
                '2013/11/27/baz/7.txt',
            ],
            [
                '2019/01/10/baz/8.txt',
            ],
            null,
        ];
        // Case #1.
        $out[] = [
            // Yesterday ago all providers.
            new \DateTime('2013-11-26 00:00:00'),
            $allFiles,
            [
                '2012/11/10/foo/1.txt',
                '2012/11/10/bar/2.txt',
                '2012/11/10/baz/20_20_20_hash_uniqid/3.txt',
                '2013/01/10/baz/4.txt',
                '2013/04/10/baz/5.txt',
                '2013/08/10/baz/6.txt',
            ],
            [
                '2013/11/27/baz/7.txt',
                '2019/01/10/baz/8.txt',
            ],
            null,
        ];
        // Case #2.
        $out[] = [
            // All providers 6 months ago.
            new \DateTime('2013-05-27 00:00:00'),
            $allFiles,
            [
                '2012/11/10/foo/1.txt',
                '2012/11/10/bar/2.txt',
                '2012/11/10/baz/20_20_20_hash_uniqid/3.txt',
                '2013/01/10/baz/4.txt',
                '2013/04/10/baz/5.txt',
            ],
            [
                '2013/08/10/baz/6.txt',
                '2013/11/27/baz/7.txt',
                '2019/01/10/baz/8.txt',
            ],
            null,
        ];
        // Case #3.
        $out[] = [
            // With provider "baz" 6 months ago.
            new \DateTime('2013-05-27 00:00:00'),
            $allFiles,
            [
                '2012/11/10/baz/20_20_20_hash_uniqid/3.txt',
                '2013/01/10/baz/4.txt',
                '2013/04/10/baz/5.txt',
            ],
            [
                '2012/11/10/foo/1.txt',
                '2012/11/10/bar/2.txt',
                '2013/08/10/baz/6.txt',
                '2013/11/27/baz/7.txt',
                '2019/01/10/baz/8.txt',
            ],
            ['baz'],
        ];
        // Case #4.
        $out[] = [
            // With provider "baz"  and "bar" 6 months ago.
            new \DateTime('2013-05-27 00:00:00'),
            $allFiles,
            [
                '2012/11/10/baz/20_20_20_hash_uniqid/3.txt',
                '2012/11/10/bar/2.txt',
                '2013/01/10/baz/4.txt',
                '2013/04/10/baz/5.txt',
            ],
            [
                '2012/11/10/foo/1.txt',
                '2013/08/10/baz/6.txt',
                '2013/11/27/baz/7.txt',
                '2019/01/10/baz/8.txt',
            ],
            ['baz', 'bar'],
        ];

        return $out;
    }

    /**
     * Cleanup Before function test.
     *
     * @param \DateTime $mockedNow
     * @param array     $fixture
     * @param array     $deleted
     * @param array     $existing
     * @param array     $providers
     *
     * @dataProvider getTestCleanupBeforeData
     */
    public function testCleanupBefore($mockedNow, $fixture, $deleted, $existing, $providers)
    {
        $fileSystem = new Filesystem();

        /** @var DateHelper|\PHPUnit_Framework_MockObject_MockObject $helper */
        $helper = $this->getMock('ONGR\ConnectionsBundle\Service\DateHelper');
        $helper->expects($this->any())->method('convertDate')->will($this->returnValue($mockedNow));
        $dir = new ImportDataDirectory(sys_get_temp_dir(), 'ongr');

        $dir->setDateHelper($helper);

        $pathPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ongr' . DIRECTORY_SEPARATOR;

        foreach ($fixture as $file) {
            $path = $pathPrefix . $file;
            $fileSystem->mkdir(dirname($path));
            $fileSystem->dumpFile($path, '.');
        }

        $dir->cleanUp($providers, 'doesnt matter');

        foreach ($deleted as $file) {
            $this->assertFileNotExists($pathPrefix . $file);
        }
        foreach ($existing as $file) {
            $this->assertFileExists($pathPrefix . $file);
        }
    }

    /**
     * @return array
     */
    public function getTestLocateFileData()
    {
        $out = [];

        // Case #0.
        $out[] = ['/var/data/file.json', '/var/www/app', 'data', '/var/data/file.json'];

        // Case #1.
        $out[] = ['file.json', '/var/www/app', 'data', '/var/www/app/data/file.json'];

        // Case #2.
        $out[] = [substr(__FILE__, strlen(dirname(dirname(__FILE__))) + 1), dirname(__FILE__), 'data', __FILE__];

        return $out;
    }

    /**
     * Locate file functional test.
     *
     * @param string $file
     * @param string $app
     * @param string $data
     * @param string $expected
     *
     * @dataProvider getTestLocateFileData()
     */
    public function testLocateFile($file, $app, $data, $expected)
    {
        $dir = new ImportDataDirectory($app, $data);

        $this->assertEquals($expected, $dir->locateFile($file));
    }

    /**
     * @return ImportDataDirectory
     */
    protected function getService()
    {
        return self::createClient()->getContainer()->get('ongr_connections.import_data_directory');
    }
}
