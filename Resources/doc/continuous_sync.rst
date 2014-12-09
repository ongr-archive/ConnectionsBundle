Continuous Synchronization
==========================

Example extractor setup for sql:

.. code-block:: yml

    parameters:
        ongr_project.sql_relations.simple_trigger.class: ONGR\ConnectionsBundle\Sync\Extractor\Relation\SimpleSqlRelation
        ongr_project.sql_relations.composed_trigger.class: ONGR\ConnectionsBundle\Sync\Extractor\Relation\ComposedSqlRelation
        ongr_project.sql_relations.related_table.class: ONGR\ConnectionsBundle\Sync\Extractor\Relation\JoinStatement
        ongr_project.sql_relations.category.fields:
            - OXTITLE
    
    services:
        ongr_project.sql_relations.category.create:
            class: %ongr_project.sql_relations.composed_trigger.class%
            arguments: [oxcategories, C, 1, category, NEW.OXID]
            tags:
                - { name: ongr_connections.sql_relation }
            calls:
                - [ addStatement, [ @ongr_project.sql_relations.category.join.articles ] ]
    
        ongr_project.sql_relations.category.delete:
            class: %ongr_project.sql_relations.composed_trigger.class%
            arguments: [oxcategories, D, 1, category, OLD.OXID]
            tags:
                - { name: ongr_connections.sql_relation }
            calls:
                - [ addStatement, [ @ongr_project.sql_relations.category.join.articles ] ]
    
        ongr_project.sql_relations.category.update:
            class: %ongr_project.sql_relations.composed_trigger.class%
            arguments: [oxcategories, U, 1, category, __ctx__.OXID]
            arguments: [oxcategories, U, 1, category, __ctxo__.OXID, %ongr_project.sql_relations.category.fields%]
            tags:
                - { name: ongr_connections.sql_relation }
            calls:
                - [ addStatement, [ @ongr_project.sql_relations.category.join.articles ] ]
    
        ongr_project.sql_relations.category.join.articles:
            class: %ongr_project.sql_relations.related_table.class%
            arguments: [oxobject2category AS oc, oc.OXOBJECTID, oc.OXCATNID=NEW.OXID, product, U, 1]
            
..
