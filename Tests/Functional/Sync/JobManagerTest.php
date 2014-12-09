<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync;

use ONGR\ConnectionsBundle\Entity\SyncJob;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\JobManager;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;

/**
 * Class JobManagerTest.
 *
 * Tests methods of JobManager which require database
 */
class JobManagerTest extends TestBase
{
    /**
     * Gets an array of dummy sync jobs for testing.
     *
     * @return SyncJob[]
     */
    private function getDummyJobs()
    {
        /** @var SyncJob $job0 This job should be found after query. */
        $job0 = new SyncJob();
        $job0->setDocumentId('0');
        $job0->setDocumentType('documentType0');
        $job0->setTimestamp(new \DateTime('2012-07-08 11:14:15'));
        $job0->setUpdateType('updateType0');
        $job0->setStatus(SyncJob::STATUS_NEW);
        $job0->setType('type0');

        /** @var SyncJob $job1 This job shouldn't be found after query. */
        $job1 = new SyncJob();
        $job1->setDocumentId('1');
        $job1->setDocumentType('type1');
        $job1->setTimestamp(new \DateTime('2012-06-08 11:15:15'));
        $job1->setUpdateType('documentType1');
        $job1->setStatus(SyncJob::STATUS_DONE);
        $job1->setType('type1');

        /** @var SyncJob $job2 This job should be found after query. */
        $job2 = new SyncJob();
        $job2->setDocumentId('2');
        $job2->setDocumentType('type2');
        $job2->setTimestamp(new \DateTime('2012-08-08 11:16:15'));
        $job2->setUpdateType('documentType2');
        $job2->setStatus(SyncJob::STATUS_NEW);
        $job2->setType('type2');

        $entityManager = $this->getEntityManager();
        $entityManager->persist($job0);
        $entityManager->persist($job1);
        $entityManager->persist($job2);

        return [$job0, $job1, $job2];
    }

    /**
     * Test for getWaitingJobs method.
     */
    public function testGetWaitingJobs()
    {
        $jobs = $this->getDummyJobs();
        $this->importData('JobManagerTest/table.sql');
        $entityManager = $this->getEntityManager();
        $entityManager->flush();

        $jobManager = new JobManager($entityManager);

        $waitingJobs = $jobManager->getWaitingJobs(1000);
        $this->assertEquals([$jobs[0], $jobs[2]], $waitingJobs);

        $waitingJobs = $jobManager->getWaitingJobs(1);
        $this->assertEquals([$jobs[0]], $waitingJobs);
    }

    /**
     * Test flush done jobs.
     */
    public function testFlushDoneJobs()
    {
        $this->importData('JobManagerTest/table.sql');
        $jobs = $this->getDummyJobs();
        $entityManager = $this->getEntityManager();

        $jobManager = new JobManager($entityManager);
        $jobManager->flushDoneJobs($jobs);

        $this->assertEquals($jobs, $entityManager->getRepository('ONGRConnectionsBundle:SyncJob')->findAll());
    }
}
