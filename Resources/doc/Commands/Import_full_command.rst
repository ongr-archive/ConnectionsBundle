Full Import Command
===================

Full import command, invoked by ``ongr:import:full``.

Imports all the data in your defined sources to your defined destinations.

It extends `AbstractStartServiceCommand <Internals/Abstract_Start_Service_Command.rst>`_, so it accepts [PIPELINE_NAME] optional parameter.

Command usage
~~~~~~~~~~~~~

::

    app/console ongr:import:full [PIPELINE_NAME]


``PIPELINE_NAME`` sets custom pipeline name and defaults to "default".

See `Import documentation's <../Import/import.rst>`_ section "Using different pipeline names" for more information.

For more information on how to set up import, refer to `Import documentation <../Import/import.rst>`_.
