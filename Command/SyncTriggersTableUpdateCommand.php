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

use Doctrine\DBAL\Connection;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command which updates sync jobs table.
 */
class SyncTriggersTableUpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:triggers:table-update')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'DBAL Connection to use.')
            ->setDescription('Updates sync jobs table');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TableManager $tableManager */
        $tableManager = $this->getContainer()->get('ongr_connections.sync.table_manager');

        $connectionId = $input->getOption('connection');
        $connection = null;

        if ($connectionId) {
            if (!$this->getContainer()->has("doctrine.dbal.{$connectionId}_connection")) {
                throw new \InvalidArgumentException("DBAL connection with ID '{$connectionId}' was not found.");
            }
            /** @var Connection $connection */
            $connection = $this->getContainer()->get("doctrine.dbal.{$connectionId}_connection");
        }

        $result = $tableManager->updateTable($connection);

        if ($result === null) {
            $output->writeln('<info>Table is up to date.</info>');
        } elseif ($result === true) {
            $output->writeln('<info>Table successfully updated.</info>');
        } else {
            $output->writeln('<error>Failed to update table. Create it before trying to update.</error>');
        }
    }
}
