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
                'Use --set="<new value>" to set new parameter value'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameter = $input->getArgument('parameter');
        $set = $input->getOption('set');

        /** @var PairStorage $pairStorage */
        $pairStorage = $this->getContainer()->get('ongr_connections.pair_storage');

        if (isset($parameter) & !empty($parameter)) {
            $setValue = $pairStorage->get($parameter);

            $output->writeln(
                "Parameter `$parameter`: " .
                ($setValue === null ? 'has no value.' : var_export($setValue, true))
            );

            if ($set) {
                $pairStorage->set($parameter, $set);
                $output->writeln('New value written: ' . var_export($set, true));
            } else {
                $output->writeln('If you want to write new value, use --set="<new value>" option.');
            }
        }
    }
}
