<?php

namespace mindplay\tracer;

/**
 * This class represents an ordered list of stack-trace elements and (optionally) and associated message.
 */
class Trace
{
    /**
     * @var TraceElement[]
     */
    private $elements;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var Trace|null
     */
    private $previous;

    /**
     * @param TraceElement[] $elements
     * @param string|null    $message
     * @param Trace|null     $previous
     */
    public function __construct($elements, $message = null, Trace $previous = null)
    {
        $this->elements = $elements;
        $this->message = $message;
        $this->previous = $previous;
    }

    /**
     * @return TraceElement[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return Trace|null
     */
    public function getPrevious()
    {
        return $this->previous;
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

        $previous = $this->previous
            ? $this->previous->filter($accept)
            : null;

        return new Trace($elements, $this->message, $previous);
    }
}
