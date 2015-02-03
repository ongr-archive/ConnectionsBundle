Full Import Command
===================

Full import command, invoked by ``ongr:import:full``.

Imports all the data from your defined sources to your defined destinations.

It extends `AbstractStartServiceCommand <internals/abstract_start_service_command.rst>`_, so it accepts [PIPELINE_NAME] optional parameter.

Command usage
~~~~~~~~~~~~~

::

    app/console ongr:import:full [PIPELINE_NAME]


``PIPELINE_NAME`` sets custom pipeline name and defaults to "default".

See `Import documentation's <../import/import.rst>`_ section "Using different pipeline names" for more information.

For more information on how to set up import, refer to `Import documentation <../import/import.rst>`_.
