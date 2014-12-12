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
use InvalidArgumentException;
use ONGR\ConnectionsBundle\Sync\StorageManager\StorageManagerInterface;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command which creates storage place for SyncStorage data.
 */
class SyncStorageCreateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:storage:create')
            ->setDescription('Creates storage place for SyncStorage')
            ->addArgument(
                'storage',
                InputArgument::REQUIRED,
                'Storage to use. Available: ' . SyncStorage::STORAGE_MYSQL
            )
            ->addOption(
                'shop-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Shop id (optional)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storage = $input->getArgument('storage');
        $shopId = $input->getOption('shop-id');
        $shopId = (int)$shopId;

        switch ($storage) {
            case SyncStorage::STORAGE_MYSQL:
                if (!$this->getContainer()->has('doctrine.dbal.default_connection')) {
                    throw new InvalidArgumentException('DBAL connection was not found.');
                }
                /** @var Connection $connection */
                $connection = $this->getContainer()->get('doctrine.dbal.default_connection');
                /** @var StorageManagerInterface $storageManager */
                $storageManager = $this->getContainer()
                    ->get('ongr_connections.sync.storage_manager.mysql_storage_manager');
                break;
            default:
                throw new InvalidArgumentException('Storage "' . $storage . '" is not implemented yet.');
        }

        $result = $storageManager->createStorage($shopId, $connection);

        if ($result === null) {
            $output->writeln('<info>Storage for ' . $storage . ' already exists.</info>');
        } elseif ($result === true) {
            $output->writeln('<info>Storage successfully created for ' . $storage . '.</info>');
        } else {
            $output->writeln('<error>Failed to create storage for ' . $storage . '.</error>');
        }
    }
}
