<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\ConnectionsBundle\Entity\SyncJob;
use Psr\Log\LoggerAwareTrait;

/**
 * This is the class that manages waiting sync jobs.
 */
class JobManager
{
    use LoggerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entityNamespace;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $namespace
     */
    public function __construct(EntityManagerInterface $entityManager, $namespace = 'ONGRConnectionsBundle:')
    {
        $this->entityManager = $entityManager;
        $this->entityNamespace = $namespace;
    }

    /**
     * Marks duplicated jobs as executed.
     *
     * @param SyncJob[]|\Iterator $jobs
     *
     * @return SyncJob[]
     */
    public function cancelDuplicateJobs($jobs)
    {
        $cache = [];

        foreach ($jobs as $job) {
            $type = $job->getDocumentType();

            if (!$type) {
                if ($this->logger) {
                    $this->logger->error('Unrecognized entity');
                }
                $this->markJobDone($job);
                continue;
            }

            $objectId = $job->getDocumentId();
            if (empty($objectId)) {
                if ($this->logger) {
                    $this->logger->error('objctId value is empty');
                }
                $this->markJobDone($job);
                continue;
            }

            if (!isset($cache[$type][$job->getDocumentId()])) {
                $cache[$type][$job->getDocumentId()] = $job;
                continue;
            }

            // Ignore all older jobs if current job asks to create or remove document.
            if ($job->getType() === SyncJob::TYPE_DELETE || $job->getType() === SyncJob::TYPE_CREATE) {
                /** @var $oldJob SyncJob */
                $oldJob = $cache[$type][$job->getDocumentId()];

                if ($this->logger) {
                    $this->logger->info(
                        sprintf(
                            "Skip duplicate action '%s' on '%s' '%s'.",
                            $oldJob->getType(),
                            $type,
                            $oldJob->getDocumentId()
                        )
                    );
                }

                $this->markJobDone($oldJob);

                $cache[$type][$job->getDocumentId()] = $job;
                continue;
            }

            if ($job->getType() === SyncJob::TYPE_UPDATE) {
                /** @var $oldJob SyncJob */
                $oldJob = $cache[$type][$job->getDocumentId()];

                if ($oldJob->getType() === SyncJob::TYPE_CREATE
                    || ($oldJob->getType() === SyncJob::TYPE_UPDATE
                    && $job->getUpdateType() <= $oldJob->getUpdateType())
                ) {
                    if ($this->logger) {
                        $this->logger->info(
                            sprintf(
                                "Skip duplicate action '%s' on '%s' '%s'.",
                                $job->getType(),
                                $type,
                                $job->getDocumentId()
                            )
                        );
                    }

                    $this->markJobDone($job);
                    continue;
                }

                if ($oldJob->getType() === SyncJob::TYPE_DELETE
                    || ($oldJob->getType() === SyncJob::TYPE_UPDATE
                    && $job->getUpdateType() > $oldJob->getUpdateType())
                ) {
                    $job->setUpdateType(SyncJob::UPDATE_TYPE_FULL);

                    if ($this->logger) {
                        $this->logger->info(
                            sprintf(
                                "Skip duplicate action '%s' on '%s' '%s'.",
                                $oldJob->getType(),
                                $type,
                                $oldJob->getDocumentId()
                            )
                        );
                    }
                    $this->markJobDone($oldJob);
                    $cache[$type][$job->getDocumentId()] = $job;
                    continue;
                }
            }
        }

        $finalJobs = $this->getFinalJobs($cache);

        return $finalJobs;
    }

    /**
     * Marks synchronization job as done.
     *
     * @param SyncJob $job
     */
    public function markJobDone(SyncJob $job)
    {
        $job->setStatus(SyncJob::STATUS_DONE);
    }

    /**
     * Makes plain jobs array from multidimensional.
     *
     * @param SyncJob[] $jobs
     *
     * @return SyncJob[]
     */
    protected function getFinalJobs($jobs)
    {
        $finalJobs = [];

        foreach ($jobs as $type) {
            foreach ($type as $job) {
                $finalJobs[] = $job;
            }
        }

        return $finalJobs;
    }

    /**
     * Returns list of waiting jobs.
     *
     * @param int $limit
     *
     * @return SyncJob[]
     */
    public function getWaitingJobs($limit)
    {
        $dql = "
            SELECT job
            FROM {$this->entityNamespace}SyncJob job
            WHERE job.status = :new
        ";

        $query = $this->entityManager->createQuery($dql)
            ->setMaxResults($limit)
            ->setParameter(':new', SyncJob::STATUS_NEW);

        $jobs = $query->getResult();

        return $jobs;
    }

    /**
     * Flush done jobs.
     *
     * @param SyncJob[] $jobs
     */
    public function flushDoneJobs($jobs)
    {
        $this->entityManager->flush($jobs);
        $this->entityManager->clear();
    }

    /**
     * Returns job document type.
     *
     * @param SyncJob $job
     *
     * @return string
     */
    public function getJobType(SyncJob $job)
    {
        return $job->getDocumentType();
    }

    /**
     * Execute custom logic after whole synchronization process is done.
     */
    public function onFinish()
    {
        // Extend and add your custom logic.
    }
}
