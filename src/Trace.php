<?php

namespace mindplay\tracer;

/**
 * This class represents an ordered list of stack-trace elements.
 */
class Trace
{
    /**
     * @var TraceElement[]
     */
    private $elements;

    /**
     * @param TraceElement[] $elements
     */
    public function __construct($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return TraceElement[]
     */
    public function getElements()
    {
        return $this->elements;
    }
}
