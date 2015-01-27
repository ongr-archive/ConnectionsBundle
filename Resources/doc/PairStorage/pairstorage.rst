PairStorage
===========

PairStorage is internal service used to store run-time key-value parameters in ElasticSearch document for internal usage.


Initial configuration
---------------------

Before using PairStorage, it's Document, along with all other ConnectionsBundle documents, must be registered in ElasticSearch manager, i.e.:

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

To use PairStorage, just get ``ongr_connections.pair_storage`` service from container:

.. code-block:: php

  /** @var PairStorage $pairStorage */
  $pairStorage = $this->getContainer()->get('ongr_connections.pair_storage');

..
