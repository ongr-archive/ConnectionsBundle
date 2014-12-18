==========================
MySQL Binlog Diff Provider
==========================

Gets all changes from MySQL binlog by parsing it.

Setup
-----

Enable binlog in MYSQL
~~~~~~~~~~~~~~~~~~~~~~

Enable mysql binlog

  #chmod 0644 /etc/mysql/my.cnf
  #sed "/skip-external-locking/a log-bin=mysql-bin\nbinlog_format = ROW\ndatadir = /var/lib/mysql" -i /etc/mysql/my.cnf

Add permissions to read binlog files

  #usermod -g travis mysql
  #chmod -R 0777 /var/lib/mysql

Restart MySQL

  #service mysql restart

Set Binlog Parameters in YAML configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # binlog parameters
    ongr_connections.sync.diff_provider.binlog_dir: /var/lib/mysql
    ongr_connections.sync.diff_provider.binlog_basename: mysql-bin
..

Register your source settings into YAML configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

     test.sync.data_sync.source:
        class: ONGR\ConnectionsBundle\EventListener\DataSyncSourceEventListener
        arguments:
            - @ongr_connections.sync.diff_provider.binlog_diff_provider
        tags:
            - { name: kernel.event_listener, event: ongr.pipeline.data_sync.some-target.source, method: onSource }
..
