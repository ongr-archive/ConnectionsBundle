Sync Provide Command
====================

This command is invoked via ``ongr:sync:provide``.

Populates `Sync Storage <../Sync/Storage/sync_storage.rst>`_, i.e. scans your source database
(using defined `DiffProvider <../Sync/DifProvider/diff_provider.rst>`_ and `Extractor <../Extractor/extractor.rst>`_
for relevant object changes (creates, updates and deletes) and writes change data to
`Sync Storage <../Sync/Storage/sync_storage.rst>`_.

It extends `AbstractStartServiceCommand <Internals/Abstract_Start_Service_Command.rst>`_ and therefore accepts [PIPELINE_NAME] optional parameter.

Command usage
~~~~~~~~~~~~~

::

    app/console ongr:sync:provide [PIPELINE_NAME]


``PIPELINE_NAME`` sets custom pipeline name and defaults to "default".

See `Import documentation's <../Import/import.rst>`_ section "Using different pipeline names" for more information.

For more information on how to set up sync import, refer to `Sync Import documentation <../Sync/sync.rst>`_.
