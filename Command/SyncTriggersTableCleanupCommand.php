<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Command;

use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\JobsCleanupService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command which cleans sync jobs table.
 */
class SyncTriggersTableCleanupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:triggers:table-cleanup')
            ->setDescription('Removes complete sync jobs from DB');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $start = microtime(true);
            /** @var JobsCleanupService $service */
            $service = $this->getContainer()->get('ongr_connections.jobs_cleanup_service');

            $service->doCleanup();

            $output->writeln('');
            $output->writeln(sprintf('<info>Job finished in %.2f s</info>', microtime(true) - $start));

        } catch (\Exception $e) {
            $output->writeln('<error>Something went really wrong!!!</error>');
            $output->writeln('<error>' . $e . '</error>');
        }
        $output->writeln(sprintf('<info>Memory usage: %.2f MB</info>', memory_get_peak_usage() >> 20));
    }
}
