CommandBenchmark
================

Provides standardized output or execution statistics for console commands.

If outputStat parameter of finish function is set to ``true``, statistics are written to console,
otherwise returned as an array:

::

    [
        'start' => $this->start,
        'finish' => $end,
        'duration' => $end - $this->start,
        'memory_peak' => memory_get_peak_usage() >> 20,
    ];

