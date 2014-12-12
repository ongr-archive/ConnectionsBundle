==============
Import command
==============
Import command allows you to import your data from any defined source into any relevant consumer while modifying it.

Working with import command
---------------------------

Command usage
~~~~~~~~~~~~~

ongr:connections:import [PIPELINE_NAME]

PIPELINE_NAME sets custom pipeline name and defaults to "default".

See "Using different pipeline names" for more information.


Implementing your data import
-----------------------------

Defining a source (data provider)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To define your own source you need to create a source event listener.

Take, for example, this predefined ImportSourceEventListener:

.. code-block:: php

    class ImportSourceEventListener
    {
        /**
         * @var EntityManager
         */
        protected $entityManager;

        /**
         * @var string Type of source.
         */
        protected $entityClass;

        /**
         * @var Manager
         */
        protected $elasticSearchManager;

        /**
         * @var string Class name of Elasticsearch document. (e.g. Product)
         */
        protected $documentClass;

        /**
         * Gets all documents by given type.
         *
         * @return MemoryEfficientEntitiesIterator
         */
        public function getAllDocuments()
        {
            return new DoctrineImportIterator(
                $this->entityManager->createQuery('SELECT e FROM :table e', ['table' => $this->entityClass])->iterate(),
                $this->entityManager,
                $this->elasticSearchManager->getRepository($this->documentClass)
            );
        }

        /**
         * @param EntityManager $manager
         * @param string        $entityClass
         * @param Manager       $elasticSearchManager
         * @param string        $documentClass
         */
        public function __construct(EntityManager $manager, $entityClass, Manager $elasticSearchManager, $documentClass)
        {
            $this->entityManager = $manager;
            $this->entityClass = $entityClass;
            $this->elasticSearchManager = $elasticSearchManager;
            $this->documentClass = $documentClass;
        }

        /**
         * Gets data and adds source.
         *
         * @param SourcePipelineEvent $event
         */
        public function onSource(SourcePipelineEvent $event)
        {
            $event->addSource($this->getAllDocuments());
        }
    }

..

In this example getAllDocuments() method will return all needed data.
It is very important to have onSource() method included, which defines the behaviour of your source event.

Next step is adding your source settings into YAML configuration:

.. code-block:: yaml

    my.import.source:
           class: %my.import.source.class%
           parent: ongr_connections.import.source
           arguments:
             - @doctrine.orm.my_entity_manager
             - %my.doctrine.entity.class%
             - @es.manager
             - %my.elasticsearch.entity.class%
           tags:
             - { name: kernel.event_listener, event: ongr.pipeline.import.default.source, method: onSource }

..


Defining a modifier
~~~~~~~~~~~~~~~~~~~

Defining a data modifier event listener is revolving around the same pattern.

Create modifier event listener class, configure YAML.

Example:

.. code-block:: php

    class ImportModifyEventListener extends AbstractImportModifyEventListener
    {
        /**
         * Assigns raw data to given object.
         *
         * @param DocumentInterface $document
         * @param mixed             $data
         */
        protected function assignDataToDocument(DocumentInterface $document, $data)
        {
            foreach ($data as $property => $value) {
                if (property_exists(get_class($document), $property)) {
                    $document->$property = $value;
                }
            }
        }

        /**
         * Modifies EventItem.
         *
         * @param EventItem $eventItem
         *
         * @return EventItem
         */
        protected function modify(EventItem $eventItem)
        {
            $this->assignDataToDocument($eventItem->getElasticItem(), $eventItem->getDoctrineItem());

            return $eventItem;
        }
    }

..


.. code-block:: yaml

       my.import.modifier:
           class: %my.import.modifier.class%
           parent: ongr_connections.import.modifier
           tags:
             - { name: kernel.event_listener, event: ongr.pipeline.import.default.modify, method: onModify }


..


Defining a consumer
~~~~~~~~~~~~~~~~~~~

Consumers are rather similar to modifiers with one key difference: while modifiers are expected to modify items, consumers are to consume items, e.g. put them into database.

The definition is roughly the same as all event listeners:

Create modifier event listener class, configure YAML.

Example:

.. code-block:: php

    class ImportConsumeEventListener implements LoggerAwareInterface
    {
        use LoggerAwareTrait;

        /**
         * @var Manager $manager
         */
        protected $manager;

        /**
         * @param Manager $manager
         */
        public function __construct(Manager $manager)
        {
            $this->manager = $manager;
        }

        /**
         * Consume event.
         *
         * @param ItemPipelineEvent $event
         *
         * @return bool
         */
        public function onConsume(ItemPipelineEvent $event)
        {
            /** @var DocumentInterface $document */
            $document = $event->getItem()->getElasticItem();

            if ($document->getId() === null) {
                if ($this->logger) {
                    $this->logger->notice('No document id found. Update skipped.');
                }

                return false;
            }

            if ($this->logger) {
                $this->logger->debug(
                    'Start update single document of type ' . get_class($document) . ' id: ' . $document->getId()
                );
            }

            $this->manager->persist($document);

            if ($this->logger) {
                $this->logger->debug(
                    'End an update of a single document.'
                );
            }

            return true;
        }
    }
..


.. code-block:: yaml

       my.initial_sync_consumer:
           class: %my.initial_sync_consumer.class%
           parent: ongr_connections.initial_sync_consumer
               arguments:
                 - @es.manager
               tags:
                  - { name: kernel.event_listener, event: ongr.pipeline.initial_sync.default.consume, method: onConsume }
..


Defining start event listener
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also define some behavior on the start event, which is being processed before the start of the pipeline's loop (but after source event).

Your event will receive a StartPipelineEvent object, which contains the pipeline context and a number of items registered inside it.

To listen on start event, use something similar to this in your config:

.. code-block:: yaml

       my.initial_sync_start:
           class: %my.initial_sync_start.class%
               tags:
                  - { name: kernel.event_listener, event: ongr.pipeline.initial_sync.default.start, method: onStart }
..

Defining finish event listener
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Finish event is executed at the end of the pipeline's life cycle, so you can attach your needed custom behaviour to it, e.g. commit every change made during pipeline's loop to ElasticSearch repository.

Example:

.. code-block:: php

    class ImportFinishEventListener
    {
        /**
         * @var Manager $manager
         */
        protected $manager;

        /**
         * @param Manager $manager
         */
        public function __construct(Manager $manager)
        {
            $this->manager = $manager;
        }

        /**
         * Finish and commit.
         */
        public function onFinish()
        {
            $this->manager->commit();
        }
    }
..


.. code-block:: yaml

       my.initial_sync_finish:
           class: %my.initial_sync_finish.class%
           parent: ongr_connections.initial_sync_finish
           arguments:
             - @es.manager
           tags:
             - { name: kernel.event_listener, event: ongr.pipeline.initial_sync.default.finish, method: onFinish }
..


Using different pipeline names
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use different event names in case you have situations when it is impossible to use a single pipeline, e.g. you have different data flows (mysql->elasticsearch and elasticsearch->mongo).

Configure your event listeners to use event names in following pattern: ongr.pipeline.initial_sync.{$name}.(source | start | modify | consume | finish).

e.g.:

.. code-block:: yaml

       my.initial_sync_finish:
           class: %my.initial_sync_finish.class%
           parent: ongr_connections.initial_sync_finish
           arguments:
             - @es.manager
           tags:
             - { name: kernel.event_listener, event: ongr.pipeline.initial_sync.MySpecialEventName.finish, method: onFinish }
..

And call *ongr:connections:import* command using *{$name}*, e.g. ongr:connections:import MySpecialEventName

See command usage for usage details.
