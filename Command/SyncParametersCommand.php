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
            ->setDescription('Sets or gets parameter value for sync.')
            ->addArgument(
                'parameter',
                InputArgument::REQUIRED,
                'Parameter name'
            )
            ->addArgument(
                'value',
                InputArgument::OPTIONAL,
                'Parameter value'
            )
            ->addOption(
                'set',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify set to set parameter value'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('ongr:sync:provide:parameter');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameter = $input->getArgument('parameter');
        $value = $input->getArgument('value');
        $set = $input->getOption('set');

        /** @var PairStorage $pair_storage */
        $pair_storage = $this->getContainer()->get('ongr_connections.pair_storage');

        if (isset($parameter) & !empty($parameter)) {
            $set_value = $pair_storage->get($parameter);

            $output->writeln(
                "Parameter `$parameter`: " .
                ($set_value == null ? 'has no value.' : var_export($set_value, true))
            );

            if ($set & isset($value)) {
                $pair_storage->set($parameter, $value);
                $output->writeln('New value written: ' . var_export($value, true));
            } elseif (isset($value)) {
                $output->writeln('If you want to write new value, use --set option.');
            }
        }
    }
}
