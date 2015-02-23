UnbufferedConnectionHelper
==========================

This static helper class provides means to execute `unbuffered queries <http://php.net/manual/en/mysqlinfo.concepts.buffering.php>`_
in your import sources. In short, the results of a query are not put into memory, but instead retrieved on-request when iterating,
which cuts down the memory consumption considerably.

Currently only Doctrine with ``pdo_mysql`` driver is supported by the helper.

Using helper with Doctrine DBAL queries
---------------------------------------

Using UnbufferedConnectionHelper for DBAL is easy:

.. code-block:: php

    public function getUnbufferedDBALStatement(
        Connection $connection,
        $query,
        $bindings,
        $fetchMode = PDO::FETCH_ASSOC
    ) {
        UnbufferedConnectionHelper::unbufferConnection($connection);

        $prepared = $connection->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        if (is_array($bindings) || $bindings instanceof \Traversable) {
            foreach ($bindings as $what => $value) {
                $prepared->bindValue($what, $value);
            }
        }
        $prepared->setFetchMode($fetchMode);
        $prepared->execute();

        $connection->close(); // This part is important, please read below.

        return $prepared;
    }



Using helper with Doctrine ORM
------------------------------

Using unbuffered queries with ORM is no trickier:

.. code-block:: php

    $connection = $entityManager->getConnection();

    UnbufferedConnectionHelper::unbufferConnection($connection);

    $query = $this->entityManager->createQuery($myDQLQuery);

    $iterator = $query->iterate();

    $connection->close(); // This part is important, please read below.

    foreach ($iterator as $entity)
    {
        ...
    }


Why do you close the connection in examples?
--------------------------------------------

There can be only one ongoing query in an unbuffered connection. If you try to run another query while your unbuffered
query is running (e.g. try to run another SELECT query or try to persist your entity through the ORM on the same
connection), you will get an PDO exception.

Don't worry, your results will be available even if the control connection is closed, and Doctrine will open a new (by
default buffered) query automatically.
