====================
ONGR synchronization
====================

Imports data to ONRG ElasticSearch (ES) from Client data source.

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

Abstract provider class: :doc:`diff_provider`

2. Store data to temp storage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Formats, modifies, explodes and stores synchronization data to temp storage (abstraction).

Temp storage could be any data storage: Mysql, Redis, etc.

Abstract extractor class: :doc:`extractor`

Abstract storage class (codename): :doc:`panther`

3. Save changes to ES
~~~~~~~~~~~~~~~~~~~~~

Saves all changes to ES.

Abstract import class: :doc:`diff_import`

4. Delete data from temp storage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Deletes saved changes from temp storage (:doc:`panther`).

