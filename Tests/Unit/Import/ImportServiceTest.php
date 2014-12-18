<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Import;

use ArrayObject;
use ONGR\ConnectionsBundle\EventListener\ImportConsumeEventListener;
use ONGR\ConnectionsBundle\EventListener\ImportFinishEventListener;
use ONGR\ConnectionsBundle\Pipeline\Item\ImportItem;
use ONGR\ConnectionsBundle\EventListener\ImportModifyEventListener;
use ONGR\ConnectionsBundle\EventListener\ImportSourceEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\PipelineStarter;
use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Tests\Model\ProductModel;

class ImportServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDoctrineEntityManager()
    {
        $resultsObject = new ArrayObject(['1', '2']);
        $results = $resultsObject->getIterator();

        $doctrineEntityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['createQuery', 'clear'])
            ->getMock();

        $mockQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['iterate'])
            ->getMockForAbstractClass();
        $mockQuery->expects($this->once())->method('iterate')->will($this->returnValue($results));

        $doctrineEntityManager->expects($this->once())->method('createQuery')->will($this->returnValue($mockQuery));
        $doctrineEntityManager->expects($this->exactly(2))->method('clear')->will($this->returnValue(null));

        return $doctrineEntityManager;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDocument()
    {
        $document = $this->getMockBuilder('ONGR\ElasticsearchBundle\Document\DocumentInterface')
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $document->expects($this->any())->method('getId')->will($this->returnValue('Test'));

        return $document;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockElasticsearchManager()
    {
        $elasticsearchManager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'persist', 'commit'])
            ->getMock();

        $repository = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['createDocument'])
            ->getMock();

        $document = $this->getMockDocument();

        $elasticsearchManager->expects($this->once())->method('getRepository')->will($this->returnValue($repository));
        $elasticsearchManager->expects($this->once())->method('persist')->will($this->returnValue(true));
        $elasticsearchManager->expects($this->once())->method('commit')->will($this->returnValue(true));
        $repository->expects($this->exactly(2))->method('createDocument')->will($this->returnValue($document));

        return $elasticsearchManager;
    }

    /**
     * @param ImportFinishEventListener  $finishListener
     * @param ImportConsumeEventListener $consumeListener
     * @param ImportModifyEventListener  $eventItem
     * @param ImportModifyEventListener  $modifyListener
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDispatcher($finishListener, $consumeListener, $eventItem, $modifyListener)
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                ['ongr.pipeline.import.default.source', $this->anything()],
                ['ongr.pipeline.import.default.start', $this->anything()],
                ['ongr.pipeline.import.default.finish', $this->anything()],
                ['ongr.pipeline.import.default.consume', $this->anything()],
                ['ongr.pipeline.import.default.modify', $this->anything()]
            )
            ->willReturnOnConsecutiveCalls(
                ($this->returnValue(null)),
                ($this->returnValue(null)),
                ($this->returnValue($finishListener->onFinish() === null)),
                ($this->returnValue($consumeListener->onConsume($eventItem) === null)),
                ($this->returnValue($modifyListener->onModify($eventItem) === null))
            );

        return $dispatcher;
    }

    /**
     * Data import service test.
     */
    public function testImport()
    {
        $doctrineEntityManager = $this->getMockDoctrineEntityManager();

        $elasticsearchManager = $this->getMockElasticsearchManager();

        $document = $this->getMockDocument();

        $sourceListener = new ImportSourceEventListener($doctrineEntityManager, 'Test', $elasticsearchManager, 'Test');
        $sourceEvent = new SourcePipelineEvent();
        $sourceListener->onSource($sourceEvent);
        $sources = $sourceEvent->getSources();
        foreach ($sources as $sourceItem) {
            foreach ($sourceItem as $item) {
                unset($item);
            }
        }
        $modifyListener = new ImportModifyEventListener();
        $consumeListener = new ImportConsumeEventListener($elasticsearchManager);
        $finishListener = new ImportFinishEventListener($elasticsearchManager);
        $eventItem = new ItemPipelineEvent(new ImportItem(['Test'], $document));

        $dispatcher = $this->getMockDispatcher($finishListener, $consumeListener, $eventItem, $modifyListener);

        $dataImportService = new PipelineStarter();
        $pipelineFactory = new PipelineFactory();
        $pipelineFactory->setDispatcher($dispatcher);
        $pipelineFactory->setClassName('ONGR\ConnectionsBundle\Pipeline\Pipeline');
        $dataImportService->setPipelineFactory($pipelineFactory);
        $dataImportService->startPipeline('import.', null);
    }

    /**
     * Tests modify event assign data.
     */
    public function testModifyEventAssignData()
    {
        $document = new ProductModel();
        $data = ['id' => 1, 'title' => 'test', 'description' => 'test description'];

        $event = new ImportModifyEventListener();
        $eventItem = new ItemPipelineEvent(new ImportItem($data, $document));

        $event->onModify($eventItem);
        $this->assertEquals($data['id'], $eventItem->getItem()->getDocument()->id);
        $this->assertEquals($data['title'], $eventItem->getItem()->getDocument()->title);
        $this->assertEquals($data['description'], $eventItem->getItem()->getDocument()->description);
    }

    /**
     * Tests if consume event returns false when documentId is not set.
     */
    public function testConsumeEventNoDocumentId()
    {
        $document = new ProductModel();
        $data = [];

        $manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new ImportConsumeEventListener($manager);
        $eventItem = new ItemPipelineEvent(new ImportItem($data, $document));

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->setMethods(['log'])
            ->getMockForAbstractClass();

        $logger->expects($this->once())->method('log');

        $event->setLogger($logger);
        $event->onConsume($eventItem);
    }

    /**
     * Tests logger.
     */
    public function testLogger()
    {
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->setMethods(['log'])
            ->getMockForAbstractClass();

        $logger->expects($this->atLeastOnce())->method('log')->will($this->returnValue(null));

        $manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $document = new ProductModel();
        $data = [];

        $event = new ImportConsumeEventListener($manager);
        $event->setLogger($logger);

        $eventItem = new ItemPipelineEvent(new ImportItem($data, $document));

        $event->onConsume($eventItem);
        $document->setId('test');

        $eventItem = new ItemPipelineEvent(new ImportItem($data, $document));
        $event->onConsume($eventItem);
    }
}
