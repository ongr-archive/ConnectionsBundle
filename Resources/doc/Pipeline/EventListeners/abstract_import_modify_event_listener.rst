AbstractImportModifyEventListener
=================================

This class assigns fields from **Doctrine ORM** entity to **Elasticsearch document**.

No constructor is defined.

Has a public ``onModify(ItemPipelineEvent $event)`` method which checks whether item provided by ``$event->getItem()``
is either a `ImportItem or SyncExecuteItem <../Import/Internals/import_item.rst>`_ and, if it is either an
``ImportItem`` or a ``SyncExecuteItem`` with action type other than delete, calls own ``modify($item, $event)`` method.

Extending class must implement a public ``modify(AbstractImportItem $eventItem, ItemPipelineEvent $event)`` method.
