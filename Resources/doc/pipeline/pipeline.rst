Pipeline
========

Overview
--------

|pipeline_overview|

.. |pipeline_overview| image:: ../images/pipeline_overview.png

An example of pipeline data and process flow. Please note that source database can be anything - SQL database, web service, file, etc.

Pipeline is used to process data with 5 events:

- Source
    Listeners provide data from the source.

- Start
    Listeners are notified that item processing is about to begin, so they can, for example, lock end-database tables for.

- Modifier
    Assigns data from the source item to the relevant fields in end-item, modifies them as needed.

- Consume
    Consumes end-item as required, e.g. saves it in a repository.

- Finish
    Does whatever is expected after processing all source items, e.g. clear cache, commit bulk operations etc.

Pipeline starts with source event which provides all data which should be processed.
Then start event is fired to indicate that items are about to come,
after that pipeline iterates through all items from all sources, calling modify and consume events
with each item inside ``ItemPipelineEvent``. After the iterations are finished, finish event is fired
to notify that no more items will follow.

Pipeline can have any number of listeners for each event but for functioning pipeline
at least one source and consume listener should be provided.

Listeners should listen to ongr.pipeline.<PipelineName>.<Target>.<Event> events.
Example:

.. code-block:: yaml

    test.import.modifier:
        class: %ongr_connections.import.modifier.class%
        tags:
            - { name: kernel.event_listener, event: ongr.pipeline.import.default.modify, method: onModify }
..

Abstract classes are provided to facilitate development of third-party event listeners:

- AbstractImportSourceEventListener
- AbstractImportModifyEventListener
- AbstractImportConsumeEventListener
- AbstractConsumeEventListener


Item skipping
-------------
If item for some reason should be skipped without stopping pipeline, ItemSkipper static class can be used.

When modifier invokes ``ItemSkipper::skip`` method, it sets ``ItemSkip`` object in ``ItemPipelineEvent`` with a reason
for skipping (optional).

If ``AbstractConsumeEventListener`` is used and ``ItemSkip`` is set, ``skip`` method will be called.
Otherwise ``consume`` will be invoked.

Progress bars
-------------

There are two ways to make pipeline report its' progress:

Using pipeline factory
~~~~~~~~~~~~~~~~~~~~~~

Set `ProgressBar` instance for factory:

.. code-block:: php

    $service = $this->getContainer()->get('factory_name');
    $service->setProgressBar(new ProgressBar($output));
    $service->startPipeline($prefix, $input->getArgument('target'));

..

Using pipeline instance
~~~~~~~~~~~~~~~~~~~~~~~

Set `ProgressBar` instance for specific pipeline instance

.. code-block:: php

    $pipeline = new Pipeline('pipeline_name');
    $pipeline->setProgressBar(new ProgressBar($output));
    $pipeline->start();

..


Event listeners provided by ConnectionsBundle
---------------------------------------------

There are several abstract classes to extend upon if you wish to implement your own pipeline:

.. toctree::
        :maxdepth: 1
        :glob:

        event_listeners/abstract_import_source_event_listener
        event_listeners/abstract_import_modify_event_listener
        event_listeners/abstract_import_consume_event_listener
        event_listeners/abstract_consume_event_listener
