AbstractImportSourceEventListener
=================================

This class gets items from **Doctrine ORM** and creates empty **Elasticsearch documents**.

Constructor arguments are as follows:

.. code-block:: php
    /**
     * @param EntityManager $manager
     * @param string        $entityClass
     * @param Manager       $elasticsearchManager
     * @param string        $documentClass
     */
..

Where ``EntityManager`` is Doctrines' entity manager and ``Manager`` is Elasticsearch manager provided by ElasticsearchBundle.

Extending class must implement a public ``onSource(SourcePipelineEvent $event)`` method.
