Sync Provider Parameters Command
================================

This command is invoked via ``ongr:sync:provide:parameter``.

Sets or gets parameter used by Sync Import provider, e.g. last import date.

Uses `pair storage <../pair_storage/index.rst>`_ to store values.

Command usage
~~~~~~~~~~~~~

::

    app/console ongr:sync:provide:parameter [--set="VALUE"] parameter.name


Where ``VALUE`` is the value you wish to store and ``parameter.name`` is the field identifier.
