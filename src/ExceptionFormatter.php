<?php

namespace mindplay\tracer;

use Exception;
use ErrorException;

/**
 * This class creates formatted Exception messages with stack-traces.
 */
class ExceptionFormatter
{
    /**
     * @var TraceFactory
     */
    protected $factory;

    /**
     * @var TraceFormatter
     */
    protected $trace_formatter;

    /**
     * @param TraceFactory|null   $factory
     * @param TraceFormatter|null $trace_formatter
     */
    public function __construct(TraceFactory $factory = null, TraceFormatter $trace_formatter = null)
    {
        $this->factory = $factory ?: $this->createDefaultFactory();
        $this->trace_formatter = $trace_formatter ?: $this->createDefaultTraceFormatter();
    }

    /**
     * Format an Exception, with stack-traces, optionally backtracking through previous Exceptions.
     *
     * @param Exception $exception
     * @param int       $backtracking maximum number of levels to backtrack through previous exceptions
     *
     * @return string
     */
    public function formatException(Exception $exception, $backtracking = 0)
    {
        $message = $this->formatExceptionMessage($exception);

        $exception_trace = $this->factory->createFromException($exception);

        $message .= "\n\n" . $this->trace_formatter->formatTrace($exception_trace);

        $previous_exception = $exception->getPrevious();

        while ($backtracking > 0 && $previous_exception) {
            $message .= "\n\nPrevious " . $this->formatExceptionMessage($previous_exception);

            $previous_trace = $this->factory->createFromException($previous_exception);

            $message .= "\n\n" . $this->trace_formatter->formatTrace($previous_trace);

            $backtracking -= 1;

            $previous_exception = $backtracking > 0
                ? $previous_exception->getPrevious()
                : null;
        }

        return $message;
    }

    /**
     * @return TraceFactory
     */
    protected function createDefaultFactory()
    {
        return new TraceFactory();
    }

    /**
     * @return TraceFormatter
     */
    protected function createDefaultTraceFormatter()
    {
        return new TraceFormatter();
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    protected function formatExceptionMessage(Exception $exception)
    {
        $type = $this->formatExceptionType($exception);
        $message = $exception->getMessage() ?: '{none}';

        return "{$type} with message: {$message}";
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    protected function formatExceptionType(Exception $exception)
    {
        $type = get_class($exception);

        if ($exception instanceof ErrorException) {
            switch ($exception->getSeverity()) {
                case E_ERROR:
                case E_USER_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                    return "{$type}: Fatal error";

                case E_PARSE:
                    return "{$type}: Parse error";

                case E_WARNING:
                case E_USER_WARNING:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                    return "{$type}: Warning";

                case E_NOTICE:
                case E_USER_NOTICE:
                    return "{$type}: Notice";

                case E_STRICT:
                    return "{$type}: Strict standards";

                case E_RECOVERABLE_ERROR:
                    return "{$type}: Catchable error";

                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    return "{$type}: Deprecated";
            }

            return "{$type}: Unknown error";
        }

        return $type === Exception::class
            ? "Exception"
            : "Exception: {$type}";
    }
}
