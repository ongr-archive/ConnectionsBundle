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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ONGR\ConnectionsBundle\Service\PairStorage;

/**
 * Command which sets/gets sync run-time parameters.
 */
class SyncParametersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:provide:parameter')
            ->setDescription('Sets or gets parameter value for sync.')
            ->addArgument(
                'parameter',
                InputArgument::REQUIRED,
                'Parameter name'
            )
            ->addOption(
                'set',
                null,
                InputOption::VALUE_REQUIRED,
                'Use --set to set new parameter value'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameter = $input->getArgument('parameter');
        $set = $input->getOption('set');

        /** @var PairStorage $pair_storage */
        $pair_storage = $this->getContainer()->get('ongr_connections.pair_storage');

        if (isset($parameter) & !empty($parameter)) {
            $set_value = $pair_storage->get($parameter);

            $output->writeln(
                "Parameter `$parameter`: " .
                ($set_value === null ? 'has no value.' : var_export($set_value, true))
            );

            if ($set) {
                $pair_storage->set($parameter, $set);
                $output->writeln('New value written: ' . var_export($set, true));
            } else {
                $output->writeln('If you want to write new value, use --set="<new value>" option.');
            }
        }
    }
}
