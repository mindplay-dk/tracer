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
        $elements = $this->createElementsFromException($exception);

        return $this->createTrace($elements);
    }

    /**
     * @param array $data an stack-trace record
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
     * @return TraceElement
     */
    protected function createElement($record)
    {
        return new TraceElement($record);
    }

    /**
     * Create stack-trace Elements for a given Exception
     *
     * @param Exception $exception
     *
     * @return TraceElement[]
     */
    protected function createElementsFromException(Exception $exception)
    {
        $data = $exception->getTrace();

        array_unshift($data, $this->createDataFromException($exception));

        return $this->createElementsFromData($data);
    }

    /**
     * Create stack-trace-like data for the origin call defined by a given Exception.
     *
     * @param Exception $exception
     *
     * @return array
     */
    protected function createDataFromException(Exception $exception)
    {
        $data = [];

        $file = $exception->getFile();

        if ($file !== null && $file !== "") {
            $data['file'] = $file;
        }

        $line = $exception->getLine();

        if ($line !== null && $line !== "") {
            $data['line'] = $line;
        }

        return $data;
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
