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
     * @param Exception $exception
     *
     * @return string
     */
    public function formatMessage(Exception $exception)
    {
        $type = $this->getMessageType($exception);

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
    protected function getMessageType(Exception $exception)
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
