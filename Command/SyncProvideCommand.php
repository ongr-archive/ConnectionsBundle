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

use ONGR\ConnectionsBundle\Sync\DataSyncService;
use ONGR\ConnectionsBundle\Service\ImportItem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to start synchronization pipeline process.
 */
class SyncProvideCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:provide')
            ->setDescription('Starts data synchronization pipeline')
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'Set a specific pipeline event name.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $benchmark = new CommandBenchmark($output);
        $benchmark->start();

        /** @var DataSyncService $service */
        $service = $this->getContainer()->get('ongr_connections.sync.data_sync_service');
        $service->startPipeline($input->getArgument('target'));

        $output->writeln('<info>Success.</info>');
        $benchmark->finish();
    }
}
