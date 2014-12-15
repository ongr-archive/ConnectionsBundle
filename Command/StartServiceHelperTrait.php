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
 * Simplifies command execute.
 */
trait StartServiceHelperTrait
{
    /**
     * Starts service by provided parameters.
     *
     * @param InputInterface         $input
     * @param OutputInterface        $output
     * @param PipelineExecuteService $service
     * @param string                 $prefix
     */
    private function start(InputInterface $input, OutputInterface $output, $service, $prefix)
    {
        $benchmark = new CommandBenchmark($output);
        $benchmark->start();

        /** @var PipelineExecuteService $service */
        $service->executePipeline($prefix, $input->getArgument('target'));

        $benchmark->finish();
    }

    /**
     * Adds argument with standard parameters.
     *
     * @param ContainerAwareCommand $command
     */
    private function addStandardArgument($command)
    {
        $command->addArgument(
            'target',
            InputArgument::OPTIONAL,
            'Set a specific pipeline event name.'
        );
    }
}
