<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Command;

use ONGR\ConnectionsBundle\Command\ImportFullCommand;
use ONGR\ConnectionsBundle\Tests\Functional\ESDoctrineTestCase;
use ONGR\TestingBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional test for ongr:connections:import command.
 */
class ImportFullCommandTest extends ESDoctrineTestCase
{
    /**
     * Check if a document is saved as expected after collecting data from providers.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();
        $this->importData('ImportCommandTest/products.sql');

        $manager = $this->getManager();
        $repository = $manager->getRepository('ONGRTestingBundle:Product');

        $application = new Application($kernel);
        $application->add(new ImportFullCommand());
        $command = $application->find('ongr:import:full');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $search = $repository->createSearch();

        // Temporary workaround for ESB issue #34 (https://github.com/ongr-io/ElasticsearchBundle/issues/34).
        usleep(90000);

        $actualDocuments = iterator_to_array($repository->execute($search));
        $expectedDocument = new Product();
        $expectedDocument->id = '1';
        $expectedDocument->title = 'test_prod';
        $expectedDocument->price = '0.99';
        $expectedDocument->description = 'test_desc';
        $expectedDocument->score = 1.0;

        $expectedDocuments[] = $expectedDocument;

        $expectedDocument = new Product();
        $expectedDocument->id = '2';
        $expectedDocument->title = 'test_prod2';
        $expectedDocument->price = '7.79';
        $expectedDocument->description = 'test_desc2';
        $expectedDocument->score = 1.0;

        $expectedDocuments[] = $expectedDocument;

        sort($expectedDocuments);
        sort($actualDocuments);

        $this->assertEquals($expectedDocuments, $actualDocuments);
    }
}
