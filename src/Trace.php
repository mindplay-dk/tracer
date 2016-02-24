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

    /**
     * Create a new Trace reduced to TraceElements matched by a given filter function.
     *
     * @param callable $accept filter function of the form: function (TraceElement $element) : bool
     *
     * @return Trace filtered Trace instance
     */
    public function filter($accept)
    {
        $elements = [];

        foreach ($this->elements as $element) {
            if ($accept($element)) {
                $elements[] = $element;
            }
        }

        return new Trace($elements);
    }
}
