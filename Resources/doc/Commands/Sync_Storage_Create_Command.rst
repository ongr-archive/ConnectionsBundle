Sync Storage Create Command
===========================

This command is invoked via ``ongr:sync:storage:create``.

Creates data structure for `Sync Storage <../Sync/Storage/sync_storage.rst>`_, i.e. creates a place to store creates,
updates and deletes of relevant objects that have changed in your source database.


Command usage
~~~~~~~~~~~~~

::

    app/console ongr:sync:storage:create STORAGE [SHOP_ID]


Where ``STORAGE`` is the type of `sync storage <../Sync/Storage/sync_storage.rst>`_, e.g. ``mysql``, and ``SHOP_ID`` is
an integer identifying the shop in a multishop environment.

For more information on how to set up sync import, refer to `Sync Import documentation <../Sync/sync.rst>`_.
