Sync Provide Command
====================

This command is invoked via ``ongr:sync:provide``.

Populates `Sync Storage <../sync/storage/sync_storage.rst>`_, i.e. scans your source database
(using defined `DiffProvider <../sync/diff_provider/diff_provider.rst>`_ and `Extractor <../extractor/extractor.rst>`_
for relevant object changes (creates, updates and deletes) and writes change data to
`Sync Storage <../sync/storage/sync_storage.rst>`_.

It extends `AbstractStartServiceCommand <internals/abstract_start_service_command.rst>`_ and therefore accepts [PIPELINE_NAME] optional parameter.

Command usage
~~~~~~~~~~~~~

::

    app/console ongr:sync:provide [PIPELINE_NAME]


``PIPELINE_NAME`` sets custom pipeline name and defaults to "default".

See `Import documentation's <../import/import.rst>`_ section "Using different pipeline names" for more information.

For more information on how to set up sync import, refer to `Sync Import documentation <../sync/sync.rst>`_.
