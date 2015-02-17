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

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base function test class.
 */
abstract class TestBase extends WebTestCase
{
    /**
     * @var string
     */
    private $setUpDbFile = null;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets up required info before each test.
     */
    protected function setUp()
    {
        AnnotationRegistry::registerFile(
            'vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        $container = static::createClient()->getContainer();

        $name = $container->getParameter('database_name');

        $connection = DriverManager::getConnection(
            [
                'driver' => $container->getParameter('database_driver'),
                'host' => $container->getParameter('database_host'),
                'port' => $container->getParameter('database_port'),
                'user' => $container->getParameter('database_user'),
                'password' => $container->getParameter('database_password'),
                'charset' => 'UTF8',
            ]
        );
        $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($name);

        $connection->getSchemaManager()->dropAndCreateDatabase($name);
        $connection->close();

        if ($this->getSetUpDbFile() !== null) {
            $this->executeLargeSqlFile(static::getRootDir($container) . $this->getSetUpDbFile());
        }
    }

    /**
     * Deletes the database.
     */
    public static function tearDownAfterClass()
    {
        $container = static::createClient()->getContainer();
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        $connection = $entityManager->getConnection();
        $name = $container->getParameter('database_name');
        $name = $connection->getSchemaManager()->getDatabasePlatform()->quoteSingleIdentifier($name);

        $connection->getSchemaManager()->dropDatabase($name);
    }

    /**
     * Gets entity manager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceContainer()->get('doctrine')->getManager();
    }

    /**
     * Imports sql file for testing.
     *
     * @param string $file
     */
    public function importData($file)
    {
        $this->executeSqlFile($this->getConnection(), 'Tests/Functional/Fixtures/' . $file);
    }

    /**
     * Set full route to db file.
     *
     * @param string $dbFile
     */
    public function setSetUpDbFile($dbFile)
    {
        $this->setUpDbFile = $dbFile;
    }

    /**
     * Return full route to db file.
     *
     * @return string
     */
    public function getSetUpDbFile()
    {
        return $this->setUpDbFile;
    }

    /**
     * Returns service container, creates new if it does not exist.
     *
     * @return ContainerInterface
     */
    protected function getServiceContainer()
    {
        if ($this->container === null) {
            $this->container = self::createClient()->getContainer();
        }

        return $this->container;
    }

    /**
     * Gets Connection from container.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        /** @var $doctrine RegistryInterface */
        $doctrine = $this->getServiceContainer()->get('doctrine');

        return $doctrine->getConnection();
    }

    /**
     * Compares two sets of records (suited for sync jobs data comparison).
     *
     * @param array $expectedRecords
     * @param array $actualRecords
     * @param bool  $checkAllFields
     */
    protected function compareRecords($expectedRecords, $actualRecords, $checkAllFields = true)
    {
        $ignoredFields = ['timestamp'];

        if (!$checkAllFields && isset($expectedRecords[0]) && isset($actualRecords[0])) {
            $ignoredFields = array_merge(
                $ignoredFields,
                array_diff(array_keys($actualRecords[0]), array_keys($expectedRecords[0]))
            );
        }

        // Remove ignored values.
        foreach ($actualRecords as &$actualRecord) {
            foreach ($ignoredFields as $field) {
                unset($actualRecord[$field]);
            }
        }

        $this->assertEquals($expectedRecords, $actualRecords);
    }

    /**
     * Executes an SQL file.
     *
     * @param Connection $conn
     * @param string     $file
     */
    protected function executeSqlFile(Connection $conn, $file)
    {
        $sql = file_get_contents($file);
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    /**
     * Executes large SQL file.
     *
     * @param string $filename
     */
    protected function executeLargeSqlFile($filename)
    {
        $container = static::createClient()->getContainer();
        /** @var EntityManager $manager */
        $manager = $container->get('doctrine')->getManager();
        $connection = $manager->getConnection();
        $tempLine = '';
        $lines = file($filename);

        foreach ($lines as $line) {
            // Skip it if it's a comment.
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }

            // Add this line to the current segment.
            $tempLine .= $line;

            // If it has a semicolon at the end, it's the end of the query.
            if (substr(trim($line), -1, 1) == ';') {
                $connection->exec($tempLine);
                // Reset temp variable to empty.
                $tempLine = '';
            }
        }
    }

    /**
     * Return full path to kernel root dir.
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    protected static function getRootDir($container)
    {
        return $container->get('kernel')->getRootDir();
    }
}
