<?php

namespace mindplay\tracer;

use Exception;

/**
 * This factory class creates traces and trace-elements from php's native stack-trace data.
 */
class TraceFactory
{
    /**
     * @var MessageFormatter
     */
    protected $message_formatter;

    /**
     * @param MessageFormatter|null $message_formatter
     */
    public function __construct(MessageFormatter $message_formatter = null)
    {
        $this->message_formatter = $message_formatter ?: $this->createDefaultMessageFormatter();
    }

    /**
     * @param Exception $exception
     * @param int       $backtracking number of levels to backtrack through previous stack-traces
     *
     * @return Trace
     */
    public function createFromException(Exception $exception, $backtracking = 0)
    {
        $previous_exception = $backtracking > 0
            ? $exception->getPrevious()
            : null;

        $previous_trace = $previous_exception
            ? $this->createFromException($previous_exception, $backtracking - 1)
            : null;

        $elements = $this->createElementsFromData($exception->getTrace());

        $message = $this->message_formatter->formatMessage($exception);

        return $this->createTrace($elements, $message, $previous_trace);
    }

    /**
     * @param array $data a stack-trace record
     *
     * @return Trace
     */
    public function createFromData(array $data)
    {
        $elements = $this->createElementsFromData($data);

        return $this->createTrace($elements);
    }

    /**
     * @return MessageFormatter
     */
    protected function createDefaultMessageFormatter()
    {
        return new MessageFormatter();
    }

    /**
     * @param TraceElement[] $elements
     * @param string|null    $message
     * @param Trace|null     $previous
     *
     * @return Trace
     */
    protected function createTrace($elements, $message = null, Trace $previous = null)
    {
        return new Trace($elements, $message, $previous);
    }

    /**
     * @return TraceElement
     */
    protected function createElement($record)
    {
        return new TraceElement($record);
    }

    /**
     * @param array $data
     *
     * @return TraceElement[]
     */
    protected function createElementsFromData(array $data)
    {
        return array_map([$this, "createElement"], $data);
    }
}
