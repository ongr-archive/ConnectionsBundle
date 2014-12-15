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

use ONGR\ConnectionsBundle\Import\ImportService;
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $service
     */
    private function startService(InputInterface $input, OutputInterface $output, $service)
    {
        $benchmark = new CommandBenchmark($output);
        $benchmark->start();

        $service = $this->getContainer()->get($service);

        $service->startPipeline($input->getArgument('target'));

        $benchmark->finish();
    }
}
