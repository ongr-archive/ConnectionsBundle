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

use ONGR\ElasticsearchBundle\Command\AbstractElasticsearchCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Repository crawler.
 */
class RepositoryCrawlerCommand extends AbstractStartServiceCommand
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('ongr:repository-crawler:crawl', 'Repository crawler.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->start($input, $output, 'ongr_connections.repository_crawler_service', 'repository_crawler.');
    }
}
