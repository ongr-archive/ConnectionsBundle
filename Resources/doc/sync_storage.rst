===========
SyncStorage
===========

1. Storage configuration
------------------------

By default sync storage storage is set up for MySQL like this:

.. code-block:: yaml

    ongr_connections:
        sync_storage:
            mysql:
                connection: default
                table_name: ongr_sync_storage
..

By default MySQL storage engine and @doctrine.dbal.default connection will be
used for data storage. Data will be stored in "ongr_sync_storage" table
(if you initialize storage for multiple shops, there will be multiple
tables, e.g.: ongr_sync_storage_1, ongr_sync_storage_2, etc.)

2. Data provider and consumer setup
-----------------------------------

Data source for extraction is set up using "source" event listener, like that:

.. code-block:: yaml

    services:
        my.data_sync.source:
            class: ONGR\ConnectionsBundle\EventListener\DataSyncSourceEventListener
            arguments:
                - @ongr_connections.sync.diff_provider.bin_log_diff_provider
            tags:
                - { name: kernel.event_listener, event: ongr.pipeline.data_sync.<pipeline_name>.source, method: onSource }

..

Consumer which extracts data received from diff provider is set up using "consume" event listener:

.. code-block:: yaml

    services:
        my.data_sync.consume:
            class: ONGR\ConnectionsBundle\EventListener\DataSyncConsumeEventListener
            arguments:
                - @ongr_connections.sync.extractor.passthrough_extractor
            tags:
                - { name: kernel.event_listener, event: ongr.pipeline.data_sync.<pipeline_name>.consume, method: onConsume }

..

You should have at least one "source" and one "consume" event listener. You can implement your own "source" and "consume"
event listeners using ONGR\ConnectionsBundle\EventListener\DataSyncSourceEventListener and ONGR\ConnectionsBundle\EventListener\DataSyncConsumeEventListener
implementations as examples.

3. Storage initialization
-------------------------

You must initialize storage before using it. Use the following console command to do so:

    ongr:sync:storage:create <storage_engine> [--shop-id=<shop_ID>]

where <storage_engine> can only be "mysql" at the moment, shop-id is optional. If you have one shop, you can omit shop-id
option:

    $ php app/console ongr:sync:storage:create mysql

If you have multiple shops, you should call this command multiple times:

    $ php app/console ongr:sync:storage:create mysql --shop-id=1
    $ php app/console ongr:sync:storage:create mysql --shop-id=2
    $ php app/console ongr:sync:storage:create mysql --shop-id=3
    $ php app/console ongr:sync:storage:create mysql --shop-id=4

4. Running data synchronization
-------------------------------

Use the following console command to start pipeline for data import into SyncStorage storage:

    ongr:sync:execute [<pipeline_name>]

Optional <pipeline_name> is "default" by default. You might want to specify different pipeline names if you have several
data sources to import data from. Keep in mind that event listeners for SyncStorage must be configured to use <pipeline_name>.
