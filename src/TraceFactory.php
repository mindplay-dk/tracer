<?php

namespace mindplay\tracer;

use Exception;

/**
 * This factory class creates traces and trace-elements from php's native stack-trace data.
 */
class TraceFactory
{
    /**
     * @param Exception $exception
     *
     * @return Trace
     */
    public function createFromException(Exception $exception)
    {
        $elements = $this->createElementsFromData($exception->getTrace());

        return $this->createTrace($elements);
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
     * @param TraceElement[] $elements
     *
     * @return Trace
     */
    protected function createTrace($elements)
    {
        return new Trace($elements);
    }

    /**
     * @param array $record
     *
     * @return TraceElement
     */
    protected function createElement(array $record)
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
