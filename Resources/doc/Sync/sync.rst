
====================
ONGR synchronization
====================

Imports data to ONRG ElasticSearch (ES) from Client data source.

Sub-topics
----------
.. toctree::
        :maxdepth: 1
        :glob:

        */*

Client data source
------------------
Any possible data source.

 Examples:
  - DB
  - WebServices / API
  - Files

Workflow
--------
- Get data from Client data source (abstraction)
- Store data to temp storage (abstraction)
- Save changes to ES
- Delete data from temp storage

1. Get data from Client data source
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Gets Client data needed to synchronize.

Client data source could be any data provider: DB, WS, etc.

Abstract provider class: `Diff Provider <DiffProvider/diff_provider.rst>`_

2. Store data to temp storage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Formats, modifies, explodes and stores synchronization data to temp storage (abstraction).

Temp storage could be any data storage: Mysql, Redis, etc.

Abstract extractor class: `Extractor <Extractor/extractor.rst>`_

Abstract storage class (codename): `SyncStorage <Storage/sync_storage.rst>`_

3. Save changes to ES
~~~~~~~~~~~~~~~~~~~~~

Saves all changes to ES.

Abstract import class: `DiffImport <DiffImport/diff_import.rst>`_

4. Delete data from temp storage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Deletes saved changes from temp storage (`SyncStorage <Storage/sync_storage.rst>`_).

