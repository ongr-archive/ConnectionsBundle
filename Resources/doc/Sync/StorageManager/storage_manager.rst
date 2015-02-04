==============
StorageManager
==============

All commands required for the storage are executed by ``StorageManager``, which implements ``StorageManagerInterface``.
``StorageManagerInterface`` defines 4 functions that need to be implemented by any type of ``StorageManager``:

- to create a storage;
- to add a record to the storage or update existing one with new date and time;
- remove a record from storage;
- to get data from the storage.

Every function can work with multiple shops. ``MysqlStorageManager`` is the default manager.

Adding new records to the storage
---------------------------------

``StorageManager`` checks if DELETE operation exists after every INSERT operation, because any operation before DELETE
is meaningless. If ``StorageManager`` detects that DELETE operation was inserted, it removes any other operation from
``SyncStorage``.

If INSERT operation fails, ``StorageManager`` tries to find and update a record already existing in ``SyncStorage``.

Getting data from the storage
-----------------------------

Every newly inserted record on the storage has status *0*. That means record is not processed. Method ``getNextRecords()``
updates status to *1*. All of these records will be ignored by other insertion operations.
