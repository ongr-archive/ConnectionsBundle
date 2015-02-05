Continuous Synchronization
==========================

Imports updated data to ONRG ElasticSearch (ES) from client data source.

How does continuous synchronization work?
-----------------------------------------

Continuous synchronization consists of two steps:

1. Data Sync Provide (called via `Sync Provide command <../commands/sync_provide_command.rst>`_).

This step finds the changes made during the time since the last synchronization and writes ids of changed items to `synchronization storage <storage/sync_storage.rst>`_.

2. Data Sync Execute (called via `Sync Execute command <../commands/sync_execute_command.rst>`_).

This step takes ids from synchronization storage, fetches relevant items from source database,
writes updates to Elasticsearch storage and removes relevant rows from synchronization storage.

Data Sync Provide
~~~~~~~~~~~~~~~~~

Data Sync Provide process uses `Pipeline functionality <../pipeline/pipeline.rst>`_.

Data Sync Provide source event listener service uses `Diff Provider <diff_provider/diff_provider.rst>`_ service to
obtain a Diff object to be stored in `synchronization storage <storage/sync_storage.rst>`_.

Data Sync Provide consume event listener service uses `Extractor <extractor/extractor.rst>`_ to parse the Diff object
and `Sync Storage Provider <storage/sync_storage.rst>`_ to write the ids of changed objects.

Extractor on its' own accord iterates through `extractor descriptors <descriptors/descriptors.rst>`_ to ensure that objects which are "watched" are
included in the change list.

Each Descriptor iterates through `joint relations <descriptors/descriptors.rst>`_ which ensure that the related objects
are marked as changed as well.

There is no modify or finish event listener.

Data Sync Execute
~~~~~~~~~~~~~~~~~

Data Sync Execute process is also based on `Pipeline functionality <../pipeline/pipeline.rst>`_, and is similar to full import.

Data Sync Execute Source event listener retrieves the list of changes from `synchronization storage <storage/sync_storage.rst>`_,
passes them on to Modify event listener, which maps the object from the source database to Elasticsearch Document, and passes it to
Consume event listener, which in turn either persists or deletes the document and clears the processed rows from
`synchronization storage <storage/sync_storage.rst>`_.

Data sources for continuous synchronization
-------------------------------------------

Continuous synchronization provides abstract classes which can be used to implement usage of any data source:
database, WebServices/API, files, etc.

See `Diff Provider <diff_provider/diff_provider.rst>`_ for more information.

This bundle provides implementation of mysql database source using binlog.

Continuous synchronization data storage
---------------------------------------

Continuous synchronization provides abstract classes which can be used to use any synchronization data storage back-end:
mysql, redis, etc.

See `Extractor <extractor/extractor.rst>`_, `synchronization storage <storage/sync_storage.rst>`_ for more information.

This bundle provides implementation which uses mysql database as synchronization data storage back-end.

Configuration
~~~~~~~~~~~~~

See `SQL Relations documentation <descriptors/sql_relations.rst>`_ for information on how to configure what should be
included in continuous synchronization.

Sub-topics
----------
.. toctree::
        :maxdepth: 1
        :glob:

        */*
