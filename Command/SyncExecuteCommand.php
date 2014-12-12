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

use ONGR\ConnectionsBundle\Service\ImportService;
use ONGR\ConnectionsBundle\Sync\SyncImportService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command which handles data import.
 */
class SyncExecuteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:execute')
            ->setDescription('Imports data from panther.')
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

        /** @var SyncExecuteService $service */
        $service = $this->getContainer()->get('ongr_connections.sync.execute_service');

        $service->import($input->getArgument('target'));

        $benchmark->finish();
    }
}
