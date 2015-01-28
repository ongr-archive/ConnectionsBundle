AbstractStartServiceCommand
===========================

This is an abstract class for creating console commands which use any services' (which is using pipeline) functionality.

It adds default ``target`` parameter, benchmark (see `CommandBenchmark documentation <Command_Benchmark.rst>`_) and calls services' startPipeline method.

It is the base class for all of ConnectionBundle's console commands.


