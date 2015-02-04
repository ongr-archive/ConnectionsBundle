Sql Relations
=============

Sql relations are special classes that tell extractor which fields/tables to watch for changes.

By default, all services tagged with a ``{ name: ongr_connections.sql_relation }`` are added to a single service
called ``ongr_connections.sync.relations_collection``, which later on can be passed as a parameter for
an `extractor <../extractor/extractor.rst>`_ via ``setRelationsCollection`` method.

When `extractor <../extractor/extractor.rst>`_ looks for changes, it iterates through the relations collection -
executing each relation as to mark down which changes in the database are relevant.

    NOTE: Only ``DoctrineExtractor`` supports sql relations so far.

Example extractor configuration:

.. code-block:: yaml

    # Extractor service.
    ongr_connections.sync.extractor.doctrine_extractor:
        class: ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor                    # Extractor class.
        calls:
            - [ setRelationsCollection, [ @ongr_connections.sync.relations_collection ] ] # Sql relation collection.
            - [ setConnection, [ @database_connection ] ]                                 # Database collection.
            - [ setStorageFacility, [ @ongr_connections.sync.sync_storage ] ]             # Sync storage provider.

    # Relation collection service must be configured in order to use it.
    ongr_connections.sync.relations_collection:
        class: ONGR\ConnectionsBundle\Sync\Extractor\Relation\RelationsCollection


Example simple sql relations setup:

.. code-block:: yaml

    services:
        # Relation for category creation.
        my_project.sql_relations.category.create:
            class: %ongr_connections.sql_relations.sql_relation.class%
            arguments: [my_categories, C, 1, category, NEW.categories_id]
            tags:
                - { name: ongr_connections.sql_relation }  # This tag is used to collect all sql relations.

        # Relation for category update.
        my_project.sql_relations.category.update:
            class: %ongr_connections.sql_relations.sql_relation.class%
            arguments: [my_categories, U, 1, category, NEW.categories_id]
            tags:
                - { name: ongr_connections.sql_relation }  # This tag is used to collect all sql relations.

        # Relation for category deletion
        my_project.sql_relations.category.delete:
            class: %ongr_connections.sql_relations.sql_relation.class%
            arguments: [my_categories, D, 1, category, OLD.categories_id]
            tags:
                - { name: ongr_connections.sql_relation }  # This tag is used to collect all sql relations.

..

Extractor constructor arguments are as follows:

.. code-block:: php

    /**
     * @param string      $table        Table name to hook on.
     * @param string      $type         Trigger and default job type C - create, U - update,  D - delete.
     * @param int|null    $idField      Source for document id.
     * @param int|null    $updateType   Partial update - 0, full update - 1.
     * @param string|null $documentType Type of target document.
     * @param array       $trackFields  Array of table fields to track, all using default priority.
     * @param string|null $jobType      C - create, U - update,  D - delete.
     */
..

Last two parameters can and should be left undefined, as they are to be deprecated in near future.

Cascading changes
-----------------

There are classes that help implement cascading data changes, e.g. if you have changed the name of the
category, you also have to update all the products which use said categories' name.

``Extractor\Relation\JoinStatement`` class does exactly that: you can attach it to a relation and when changes are
detected, `extractor <../extractor/extractor.rst>`_ calls the related statements and marks related documents as changed.

If ``$documentType`` is left undefined document itself will not be marked as changed but related statements will.

Example cascading change configuration:

.. code-block:: yaml

    parameters:
        ongr_connections.sql_relations.related_table.class: ONGR\ConnectionsBundle\Sync\Extractor\Relation\JoinStatement

    services:
        #
        # Create and delete relations omitted for brevity.
        #
        my_project.sql_relations.category.update:
            class: %ongr_connections.sql_relations.composed_relation.class%
            arguments: [my_categories, U, 1, category, NEW.categories_id]
            tags:
                - { name: ongr_connections.sql_relation }
            calls:
                - [ addStatement, [ @my_project.sql_relations.product.join.category ] ] # Call this statement if category is updated.

        my_project.sql_relations.product.join.category:
            class: %ongr_connections.sql_relations.related_table.class%
            arguments: [my_products_to_categories AS product_to_category, product_to_category.products_id, product_to_category.categories_id=NEW.categories_id, product, U, 1]
..

The arguments for ``JoinStatement`` are as follows:

.. code-block:: php

     /**
     * @param string $table           Related table name.
     * @param string $documentId      Document id.
     * @param string $searchCondition Escaped condition to create where sentence.
     * @param string $documentType    Target document type.
     */
..
