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
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use ONGR\ElasticsearchBundle\Test\ElasticsearchTestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base function test class. Sets up both Doctrine and Elasticsearch environments for tests.
 */
abstract class ESDoctrineTestCase extends ElasticsearchTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
     * Sets up required info before each test.
     *
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        AnnotationRegistry::registerFile(
            $this->getVendorDirectory() .
            '/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $params = $connection->getParams();
        $name = $connection->getParams()['dbname'];
        unset($params['dbname']);
        $tmpConnection = DriverManager::getConnection($params);
        $name = $tmpConnection->getDatabasePlatform()->quoteSingleIdentifier($name);
        try {
            $tmpConnection->getSchemaManager()->dropDatabase($name);
        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(), 'Error: 1009') === null) {
                throw $ex;
            }
            // Catching exception in case database not dropped.
        }
        $tmpConnection->getSchemaManager()->createDatabase($name);
        $tmpConnection->close();
    }

    /**
     * Deletes all data after each test.
     */
    protected function tearDown()
    {
        parent::tearDown();

        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $name = $connection->getParams()['dbname'];
        $name = $connection->getSchemaManager()->getDatabasePlatform()->quoteSingleIdentifier($name);
        $connection->getSchemaManager()->dropDatabase($name);
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
     * Returns path to vendors.
     *
     * @return string
     */
    private function getVendorDirectory()
    {
        // Going up 2 levels from current dir will give bundle root directory.
        $baseDir = dirname(dirname(__DIR__));
        if (basename(dirname(dirname($baseDir))) == 'vendor') {
            // If bundle is in vendors we need to remove ongr/connections-bundle.
            return basename(dirname(dirname($baseDir)));
        } else {
            // Otherwise vendors should be in bundle root directory.
            return $baseDir . DIRECTORY_SEPARATOR . 'vendor';
        }
    }
}
