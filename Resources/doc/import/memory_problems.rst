HELP! I am out of memory!
=========================

When importing large data sets there are a few things to consider. First of all, your database connections are probably
buffered by default, so your large data set goes straight to RAM (and swap, if the need arises) from your database
when you query it. If your data set is larger than the memory available (or you wish to have a more memory-efficient
import) and you are using Doctrine with ``mysql_pdo`` driver, you can use
`UnbufferedConnectionHelper <internals/unbuffered_connection_helper.rst>`_.

If your memory is disappearing in large chunks in the beginning of the import, using unbuffered connections will most
probably help.

If your memory is disappearing gradually (megabyte by megabyte), it could be that you are running your import in a
development environment. This causes symfony to use a ``TraceableEventDispatcher``, which logs debug information about
events dispatched, which, in turn, burns up precious memory.

Refer to `symfony documentation <http://symfony.com/doc/current/cookbook/configuration/environments.html>`_
if you wish to change it, or disable debugging by adding the following parameters to your configuration:

.. code-block:: yaml

    kernel.debug: false
    debug.container.dump: %kernel.cache_dir%/%kernel.container_class%.xml

which should stabilize your memory consumption, at least where ``TraceableEventDispatcher`` is concerned.
