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

use ONGR\ConnectionsBundle\Pipeline\PipelineExecuteService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AbstractStartServiceCommand -  starts service.
 */
abstract class AbstractStartServiceCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addStandardArgument();
    }

    /**
     * Starts service by provided parameters.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $serviceClass
     * @param string          $prefix
     */
    protected function start(InputInterface $input, OutputInterface $output, $serviceClass, $prefix)
    {
        $benchmark = new CommandBenchmark($output);
        $benchmark->start();

        /** @var PipelineExecuteService $service */
        $service = $this->getContainer()->get($serviceClass);
        $service->executePipeline($prefix, $input->getArgument('target'));

        $benchmark->finish();
    }

    /**
     * Adds argument with standard parameters.
     */
    protected function addStandardArgument()
    {
        $this->addArgument(
            'target',
            InputArgument::OPTIONAL,
            'Set a specific pipeline event name.'
        );
    }
}
