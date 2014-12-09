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

use ONGR\ConnectionsBundle\Command\SyncProvideCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SyncProvideCommandTest extends WebTestCase
{
    /**
     * Check if command works.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();

        $application = new Application($kernel);
        $application->add(new SyncProvideCommand());

        $command = $application->find('ongr:sync:provide');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'target' => 'some-target',
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('Success.', $output);
    }
}
