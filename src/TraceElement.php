<?php

namespace mindplay\tracer;

use OutOfBoundsException;

/**
 * This model represents a single stack-trace element.
 */
class TraceElement
{
    const TYPE_STATIC   = "::";
    const TYPE_INSTANCE = "->";

    /**
     * @var string|null
     */
    protected $file;

    /**
     * @var int|null
     */
    protected $line;

    /**
     * @var string|null
     */
    protected $function;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var string|null
     */
    protected $clazz;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var object|null
     */
    protected $object;

    /**
     * @param array $record stack-trace record
     */
    public function __construct($record)
    {
        $this->populate($record);
    }

    /**
     * @return string|null trace file-name (or NULL, if this Trace did not originate from a file)
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int|null trace line-number (or NULL, if this Trace did not originate from a file)
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string|null trace function-name (or NULL, if this Trace did not originate from a function)
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @return array list of function arguments (empty, if this Trace did not originate from a function/method-call)
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return string|null fully-qualified class-name (or NULL, if this Trace did not originate from a class)
     */
    public function getClass()
    {
        return $this->clazz;
    }

    /**
     * @return string|null one of the `TYPE_*` constants (or NULL, if this Trace did not originate from a method-call)
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return object|null object context (optionally provided by a call to `debug_backtrace()`)
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Populate this object using an individual stack-trace record, e.g. from a list of
     * stack-trace records as created by `Exception::getTrace()` or `debug_backtrace()`.
     *
     * @param array $record stack-trace record
     *
     * @return void
     *
     * @throws OutOfBoundsException if an unsupported index is encountered while populating this Trace from `$data`
     */
    protected function populate(array $record)
    {
        foreach ($record as $name => $value) {
            if ($name === "class") {
                $name = "clazz";
            }

            if (! property_exists($this, $name)) {
                throw new OutOfBoundsException("unsupported index: {$name}");
            }

            $this->$name = $value;
        }
    }
}
