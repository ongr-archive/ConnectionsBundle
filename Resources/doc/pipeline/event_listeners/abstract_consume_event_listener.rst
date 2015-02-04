AbstractConsumeEventListener
============================

This class provides logic to allow skipping of items marked for skip (See `Pipeline documentation <../pipeline.rst>`_ for more information).

There is no constructor.

This class has a public ``onConsume(ItemPipelineEvent $event)`` method, which checks if the event has a skip flag
(``$event->getItemSkip()``) and calls either ``skip($event)`` or ``consume($event)`` respectively.

Extending class must implement public ``consume(ItemPipelineEvent $event)`` method.

Implementation of public ``skip(ItemPipelineEvent $event)`` method is optional.
