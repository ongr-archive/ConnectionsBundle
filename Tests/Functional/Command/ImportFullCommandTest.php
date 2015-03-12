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
use ONGR\ConnectionsBundle\Tests\Functional\AbstractESDoctrineTestCase;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional test for ongr:connections:import command.
 */
class ImportFullCommandTest extends AbstractESDoctrineTestCase
{
    /**
     * Check if a document is saved as expected after collecting data from providers.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();
        $this->importData('ImportCommandTest/products.sql');

        $manager = $this->getManager();
        $repository = $manager->getRepository('AcmeTestBundle:Product');

        $application = new Application($kernel);
        $application->add(new ImportFullCommand());
        $command = $application->find('ongr:import:full');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $search = $repository->createSearch();

        // Temporary workaround for ESB issue #34 (https://github.com/ongr-io/ElasticsearchBundle/issues/34).
        usleep(90000);

        foreach ($repository->execute($search) as $document) {
            $actualDocuments[] = $document;
        }

        /** @var Product $expectedDocument */
        $expectedDocument = $repository->createDocument();
        $expectedDocument->__setInitialized(true);
        $expectedDocument->setId('1');
        $expectedDocument->setTitle('test_prod');
        $expectedDocument->setPrice('0.99');
        $expectedDocument->setDescription('test_desc');
        $expectedDocument->setScore(1.0);

        $expectedDocuments[] = $expectedDocument;

        $expectedDocument = $repository->createDocument();
        $expectedDocument->__setInitialized(true);
        $expectedDocument->setId('2');
        $expectedDocument->setTitle('test_prod2');
        $expectedDocument->setPrice('7.79');
        $expectedDocument->setDescription('test_desc2');
        $expectedDocument->setScore(1.0);

        $expectedDocuments[] = $expectedDocument;

        sort($expectedDocuments);
        sort($actualDocuments);

        $this->assertEquals($expectedDocuments, $actualDocuments);
    }
}
