===============
URL invalidator
===============

Configuration
-------------

Please configure your item url invalidator events as follows (or see test configuration for an example):

.. code-block:: php

    my.item_url_invalidator:
        class: %my.item_url_invalidator.class%
        parent: ongr_connections.item_url_invalidator
        calls:
            - [ setUrlInvalidator, [ @ongr_connections.url_invalidator_service ] ]
        tags:
            - { name: kernel.event_listener, event: ongr.pipeline.my_event.default.consume, method: onConsume }
            - { name: kernel.event_listener, event: ongr.pipeline.my_event.default.finish, method: onFinish }

..
