Doctrine Import Iterator class
==============================

Doctrine import iterator is a special class which can be used by `Source event listener <../pipeline/event_listeners/source.rst>`_.

It expands upon ``\IteratorIterator`` class, returns `ImportItem instance <import_item.rst>`_ upon each iteration, and,
most importantly, **cleans identity map before navigating to next record**, which makes it memory-efficient.

It can only be used with Doctrine ORM.
