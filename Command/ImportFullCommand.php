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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command which handles data import.
 */
class ImportFullCommand extends AbstractStartServiceCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ongr:import:full')->setDescription('Imports data from defined sources into consumers.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->start($input, $output, 'ongr_connections.import_service', 'import.');
    }
}
