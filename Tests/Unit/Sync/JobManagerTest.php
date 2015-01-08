<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\ConnectionsBundle\Entity\SyncJob;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\JobManager;
use Psr\Log\NullLogger;

/**
 * Unit test for JobManager.
 */
class JobManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityManager()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * Creates sync job from given data.
     *
     * @param array $data
     *
     * @return SyncJob
     */
    protected function createJob($data = [])
    {
        $job = new SyncJob();

        foreach ($data as $key => $value) {
            $job->{'set' . ucfirst($key)}($value);
        }

        return $job;
    }

    /**
     * Convert job to array.
     *
     * @param SyncJob[] $jobs
     * @param array     $fields
     *
     * @return array
     */
    protected function getJobsData($jobs, $fields = ['documentId', 'documentType', 'type'])
    {
        $result = [];

        foreach ($jobs as $job) {
            $data = [];
            foreach ($fields as $field) {
                $data[$field] = $job->{'get' . ucfirst($field)}();
            }
            $result[] = $data;
        }

        return $result;
    }

    /**
     * Test for cancelDuplicateJobs().
     *
     * @param array $jobs
     * @param array $expected
     *
     * @dataProvider getTestCancelDuplicateJobsData()
     */
    public function testCancelDuplicateJobs($jobs, $expected)
    {
        $objects = [];
        foreach ($jobs as $job) {
            $objects[] = $this->createJob($job);
        }

        $jobManager = new JobManager($this->getEntityManager());
        $jobManager->setLogger(new NullLogger());

        $result = $jobManager->cancelDuplicateJobs($objects);

        $this->assertEquals($expected, $this->getJobsData($result));
    }

    /**
     * Test for cancelDuplicateJobs() in case of iterator.
     *
     * @param array $jobs
     * @param array $expected
     *
     * @dataProvider getTestCancelDuplicateJobsData()
     */
    public function testCancelDuplicateJobsIterator($jobs, $expected)
    {
        $objects = [];
        foreach ($jobs as $job) {
            $objects[] = $this->createJob($job);
        }

        $jobManager = new JobManager($this->getEntityManager());
        $result = $jobManager->cancelDuplicateJobs(new \ArrayIterator($objects));

        $this->assertEquals($expected, $this->getJobsData($result));
    }

    /**
     * Data provider for testCancelDuplicateJobs().
     *
     * @return array[]
     */
    public function getTestCancelDuplicateJobsData()
    {
        $cases = [];

        // Case 1: two same update jobs.
        $given1 = [
            ['documentId' => 'product1', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product1', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $expected1 = [
            ['documentId' => 'product1', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $cases[] = [$given1, $expected1];

        // Case 2: delete job exists.
        $given2 = [
            ['documentId' => 'product1', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product1', 'type' => ActionTypes::DELETE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $expected2 = [
            ['documentId' => 'product1', 'type' => ActionTypes::DELETE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $cases[] = [$given2, $expected2];

        // Case 3: delete job exists before update.
        $given3 = [
            ['documentId' => 'product1', 'type' => ActionTypes::DELETE, 'documentType' => 'product'],
            ['documentId' => 'product1', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $expected3 = [
            ['documentId' => 'product1', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $cases[] = [$given3, $expected3];

        // Case 4: unrecognized document type.
        $given4 = [
            ['documentId' => 'product1', 'type' => ActionTypes::DELETE, 'documentType' => null],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product3', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $expected4 = [
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product3', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $cases[] = [$given4, $expected4];

        // Case 5: object ID is NULL.
        $given5 = [
            ['documentId' => null, 'type' => ActionTypes::DELETE, 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product3', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $expected5 = [
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
            ['documentId' => 'product3', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $cases[] = [$given5, $expected5];

        // Case 6: previous job has a lower update type.
        $given6 = [
            [
                'documentId' => 'product2',
                'type' => ActionTypes::UPDATE,
                'documentType' => 'product',
                'updateType' => SyncJob::UPDATE_TYPE_PARTIAL,
            ],
            [
                'documentId' => 'product2',
                'type' => ActionTypes::UPDATE,
                'documentType' => 'product',
                'updateType' => SyncJob::UPDATE_TYPE_FULL,
            ],
        ];
        $expected6 = [
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $cases[] = [$given6, $expected6];

        // Case 7: non existent job type, added just to make sure nothing happens after no if cases are matched.
        $given7 = [
            ['documentId' => 'product2', 'type' => 'Z', 'documentType' => 'product'],
            ['documentId' => 'product2', 'type' => ActionTypes::UPDATE, 'documentType' => 'product'],
        ];
        $expected7 = [
            ['documentId' => 'product2', 'type' => 'Z', 'documentType' => 'product'],
        ];
        $cases[] = [$given7, $expected7];

        return $cases;
    }

    /**
     * Test for markJobDone().
     */
    public function testMarkJobDone()
    {
        $jobManager = new JobManager($this->getEntityManager());

        $job = new SyncJob();
        $job->setStatus($job::STATUS_NEW);

        $jobManager->markJobDone($job);
        $this->assertEquals($job::STATUS_DONE, $job->getStatus());
    }

    /**
     * Test for getWaitingJobs().
     */
    public function testGetWaitingJobs()
    {
        $limit = 5;
        $waitingJobs = ['job1', 'job2'];

        $entityManager = $this->getEntityManager();
        $jobManager = new JobManager($entityManager);

        $query = $this->getMock('\stdClass', ['setMaxResults', 'setParameter', 'getResult']);
        $query->expects($this->once())->method('setMaxResults')->with($limit)->willReturnSelf();
        $query->expects($this->once())->method('setParameter')->willReturnSelf();
        $query->expects($this->once())->method('getResult')->willReturn($waitingJobs);

        $entityManager->expects($this->once())->method('createQuery')->willReturn($query);

        $this->assertEquals($waitingJobs, $jobManager->getWaitingJobs($limit));
    }

    /**
     * Test for flushDoneJobs().
     */
    public function testFlushDoneJobs()
    {
        $jobs = ['job1', 'job2'];

        $entityManager = $this->getEntityManager();
        $jobManager = new JobManager($entityManager);

        $entityManager->expects($this->once())->method('flush')->with($jobs);
        $entityManager->expects($this->once())->method('clear');

        $jobManager->flushDoneJobs($jobs);
    }

    /**
     * Test for getJobType().
     */
    public function testGetJobType()
    {
        $jobManager = new JobManager($this->getEntityManager());

        $job = new SyncJob();
        $job->setDocumentType('any_type');

        $this->assertEquals('any_type', $jobManager->getJobType($job));
    }

    /**
     * Since it's an empty method check if nothing changed.
     */
    public function testOnFinish()
    {
        $jobManager = new JobManager($this->getEntityManager());
        $oldJobManager = clone $jobManager;

        $jobManager->onFinish();

        $this->assertEquals($oldJobManager, $jobManager);
    }
}
