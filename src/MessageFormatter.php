<?php

namespace mindplay\tracer;

use Exception;
use ErrorException;

/**
 * This class creates formatted Exception messages.
 */
class MessageFormatter
{
    /**
     * Create a summary of an Exception type, severity, message, and file/line-number origin.
     *
     * @param Exception $exception
     *
     * @return string
     */
    public function getExceptionSummary(Exception $exception)
    {
        $type = $this->getExceptionType($exception);

        $message = $exception->getMessage() ?: '{none}';

        $file = $exception->getFile()
            ? $exception->getFile() . "(" . $exception->getLine() . ")"
            : "{no file}";

        return "{$type} with message: {$message} in {$file}";
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    public function getExceptionType(Exception $exception)
    {
        $type = get_class($exception);

        if ($exception instanceof ErrorException) {
            $severity = $exception->getSeverity();

            return "{$type}: {$severity}";
        }

        return $type === Exception::class
            ? "Exception"
            : "Exception: {$type}";
    }

    /**
     * @param int $severity severity level: one of the E_* constants defined by PHP (E_ERROR, E_NOTICE, etc.)
     *
     * @return string
     */
    public function getSeverity($severity)
    {
        switch ($severity) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                return "Fatal error";

            case E_PARSE:
                return "Parse error";

            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return "Warning";

            case E_NOTICE:
            case E_USER_NOTICE:
                return "Notice";

            case E_STRICT:
                return "Strict standards";

            case E_RECOVERABLE_ERROR:
                return "Catchable error";

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return "Deprecated";
        }

        return "Unknown error";
    }
}
