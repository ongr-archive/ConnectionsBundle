PairStorage
===========

``PairStorage`` is internal service used to store run-time key-value parameters in ``ElasticSearch`` document for internal usage.

Initial configuration
---------------------

Before using ``PairStorage``, Pair Document, along with all other ``ConnectionsBundle`` documents, must be registered in ``ElasticSearch`` manager, i.e.:

.. code-block:: yaml

  ongr_elasticsearch:
      connections:
          default:
            ...
      managers:
          default:
              connection: default
              mappings:
                  - ONGRConnectionsBundle
..

Usage
-----

``PairStorage`` is a simple service which stores key-value in ``ElasticSearch`` document.
Where:

* key - a unique string identifier
* value - any php value, defined as mixed

To use ``PairStorage``, just get ``ongr_connections.pair_storage`` service from container:

.. code-block:: php

  /** @var PairStorage $pairStorage */
  $pairStorage = $this->getContainer()->get('ongr_connections.pair_storage');
..

To get value from ``PairStorage``, use ``get($key)`` method:

.. code-block:: php

  $pairStorage->get('foo');
..

To set value, use ``set($key, $value)`` method:

.. code-block:: php

  $pairStorage->set('foo', 'bar');
..

If, for any reason, you want to completely remove ``key`` from ``PairStorage``, just use ``remove($key)`` method:

.. code-block:: php

  $pairStorage->remove('foo');
..

Setting value from command line
-------------------------------

You can use console command command ``ongr:sync:provide:parameter`` to get and set ``value`` for specific ``key``.

To get value:

.. code-block:: shell

  app/console ongr:sync:provide:parameter foo
..

To set value, use `--set='new value'` option:

.. code-block:: shell

  app/console ongr:sync:provide:parameter --set='2000.01.01 01:01:01' foo
..
