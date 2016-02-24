mindplay/tracer
===============

#### UNSTABLE / WORK IN PROGRESS

This package provides an API for working with stack-traces.

Features and objectives:

  * Provide an object-oriented API for safe work with `Exception::getTrace()` and `debug_backtrace()`
  * Format stack-traces in top-down order (consistent with php's own stack-traces)
  * Display function arguments in a familiar, human-readable format (with support for all known php data-types)
  * Handle all types of calling contexts (functions, methods, files, `eval()`'ed code, etc.)
  * Provide an open, extensible API allowing for any specialized extensions
  * Support filtering of stack-traces
  * Must operate consistently with or without xdebug installed
  * Use dependency injection everywhere (but provide meaningful defaults)


### Overview

The API consists of the following components:

  * TODO ...


### Usage

TODO


### Development

To run the test-suite:

    php -r test/test.php

The code coverage report will be output to `test/build/clover.xml`.
