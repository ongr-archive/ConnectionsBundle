Extractors
==========

Extractors are special classes used by continuous import functionality. Extractor class is responsible for marking items as changed
in `Sync Storage <storage/sync_storage.rst>`_. It "tracks" changes by reading through a diff provided by a
`Diff provider <../diff_provider/diff_provider.rst>`_.

There are two extractors provided by this bundle:
- ``PassthroughExtractor`` - as the name suggests,
a very simple extractor which depends on the storage to provide only relevant data. Provided as an example only, it is
not really used anywhere.
- ``DoctrineExtractor`` - is `the` extractor in ConnectionsBundle. This extractor relies on
`Sql relations <../relations/sql_relations.rst>`_ to distinguish which diff is relevant. It also "sees" cascading
changes, provided the `JoinStatements <../relations/sql_relations.rst>`_ are configured correctly.
