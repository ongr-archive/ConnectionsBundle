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

Abstract provider class: DiffProvider_

2. Store data to temp storage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Formats, modifies, explodes and stores synchronization data to temp storage (abstraction).

Temp storage could be any data storage: Mysql, Redis, etc.

Abstract extractor class: Extractor_

Abstract storage class (codename): Panther_

3. Save changes to ES
~~~~~~~~~~~~~~~~~~~~~

Saves all changes to ES.

Abstract import class: DiffImport_

4. Delete data from temp storage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Deletes saved changes from temp storage (Panther_).


.. _DiffProvider: diff_provider.rst
.. _Extractor: extractor.rst
.. _Panther: panther.rst
.. _DiffImport: diff_import.rst
