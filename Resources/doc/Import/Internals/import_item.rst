AbstractImportItem
==================

``AbstractImportItem`` is an Import event item carrying both source item and end-document (Elasticsearch).

``ImportItem`` extends ``AbstractImportItem`` implementing nothing extra.

``SyncExecuteItem`` extends ``AbstractImportItem``, overrides constructor to include ``$syncStorageData`` array and has
getters and setters for aforementioned array.
