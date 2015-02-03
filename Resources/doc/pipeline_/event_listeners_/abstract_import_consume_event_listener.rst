AbstractImportConsumeEventListener
==================================

This class extends upon `AbstractConsumeEventListener <abstract_consume_event_listener.rst>`_ and provides functionality
to save Elasticsearch document to Elasticsearch database.

It implements public ``consume(ItemPipelineEvent $event)`` method, which

- checks whether the item provided by ``$event->getItem()``
is of correct class (provided by ``$itemClass`` constructor argument) and has an id
(by calling protected ``setItem(ItemPipelineEvent $event)`` method).

- prepares the document to be saved in Elasticsearch (adds to bulk operation list) by calling
protected ``persistDocument(ItemPipelineEvent $event)`` method.

- logs these steps' successes and failures, provided the logger is provided according to ``Psr\Log\LoggerAwareInterface``.

Constructor arguments are as follows:

.. code-block:: php
    /**
     * @param Manager $manager
     * @param string  $itemClass
     */
..

Where ``Manager $manager`` is Elasticsearch Manager provided by ElasticsearchBundle, and ``$itemClass`` is the item class
of an item contained in ItemPipelineEvent.


