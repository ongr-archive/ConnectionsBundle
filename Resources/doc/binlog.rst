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
    ongr_connections.sync.diff_provider.binlog_start_type: 1
..

Possible values for `binlog_start_type` are:

    0 - all bin log will be parsed all the time.
    1 - `last_sync_date` will be used when reading binlog.
    2 - `last_sync_position` will be used when reading binlog. Position is integer and must be existing value from bin log, defined by: end_log_pos

To set `last_sync_date` or `last_sync_position` parameters use command `ongr:sync:provide:parameter`.
Parameter `last_sync_date` must be set to date that is in 'Y-m-d H:i:s' format, and same TimeZone as mysql.
Parameter `last_sync_position` must be set to integer value, which is existing value from binlog, defined in binlog by `end_log_pos`.

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
