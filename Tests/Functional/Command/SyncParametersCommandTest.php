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

use ONGR\ConnectionsBundle\Command\SyncParametersCommand;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\ORM\Repository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Integration test for ongr:sync:provide:parameter command.
 */
class SyncParametersCommandTest extends WebTestCase
{
    /**
     * @var ContainerAwareCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Setup before every test.
     */
    protected function setUp()
    {
        $kernel = self::createClient()->getKernel();

        $this->manager = $kernel->getContainer()->get('es.manager');

        // Clear any residual data and create indexes.
        $this->manager->getConnection()->dropAndCreateIndex();

        /** @var Application $application */
        $application = new Application($kernel);
        $application->add(new SyncParametersCommand());
        $this->command = $application->find('ongr:sync:provide:parameter');

        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Check command execute.
     */
    public function testExecute()
    {
        // Parameter was not set, so it has no value.
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'parameter' => 'test1',
            ]
        );
        $this->assertContains('Parameter `test1`: has no value.', $this->commandTester->getDisplay());

        // Lets try to give new value for parameter without --set option.
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'parameter' => 'test1',
                'value' => false,
            ]
        );
        $this->assertContains('If you want to write new value, use --set option.', $this->commandTester->getDisplay());

        // Finally, set some value, and test if it was set and returned.
        $value = '2014-01-01 01:01:01';
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'parameter' => 'test1',
                'value' => $value,
                '--set' => true,
            ]
        );
        $this->assertContains('New value written:', $this->commandTester->getDisplay());

        /** @var Repository $repo */
        $repo = $this->manager->getRepository('ONGRConnectionsBundle:Pair');

        $parameter = $repo->find('test1');
        $this->assertEquals($value, $parameter->getValue());
    }
}
