Sync Import Command
===================

This command is invoked via ``ongr:sync:execute``.

Imports data from `Sync Storage <../sync/storage/sync_storage.rst>`_, i.e. creates, updates and deletes relevant objects that have changed in your source database.

It extends `AbstractStartServiceCommand <internals/abstract_start_service_command.rst>`_, so it accepts [PIPELINE_NAME] optional parameter.

Command usage
~~~~~~~~~~~~~

::

    app/console ongr:sync:execute [PIPELINE_NAME]


``PIPELINE_NAME`` sets custom pipeline name and defaults to "default".

See `Import documentation's <../import/import.rst>`_ section "Using different pipeline names" for more information.

For more information on how to set up sync import, refer to `Sync Import documentation <../sync/sync.rst>`_.
