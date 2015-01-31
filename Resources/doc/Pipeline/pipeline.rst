Pipeline
========

Pipeline is used to process data with 5 events:

 - source
 - start
 - modify
 - consume
 - finish

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

Item skipping
-------------
If item for some reason should be skipped without stopping pipeline, ItemSkipper static class can be used.

When modifier invokes ``ItemSkipper::skip`` method, it sets ``ItemSkip`` object in ``ItemPipelineEvent`` with a reason
for skipping (optional).

If ``AbstractConsumeEventListener`` is used and ``ItemSkip`` is set, ``skip`` method will be called.
Otherwise ``consume`` will be invoked.
