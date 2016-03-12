<?php

namespace mindplay\tracer;

/**
 * This class creates formatted stack-traces.
 */
class TraceFormatter
{
    /**
     * @var ValueFormatterInterface
     */
    protected $value_formatter;

    /**
     * @param ValueFormatterInterface $value_formatter
     */
    public function __construct(ValueFormatterInterface $value_formatter = null)
    {
        $this->value_formatter = $value_formatter ?: $this->createDefaultValueFormatter();
    }

    /**
     * Create a formatted stack-trace for a given `Trace` instance.
     *
     * @param Trace $trace
     *
     * @return string
     */
    public function formatTrace(Trace $trace)
    {
        $elements = $trace->getElements();

        $formatted = [];

        foreach ($elements as $index => $element) {
            $formatted[] = sprintf("%6s", "#{$index}") . " " . $this->formatTraceElement($element);
        }

        return implode("\n", $formatted);
    }

    /**
     * @param TraceElement $element
     *
     * @return string
     */
    protected function formatTraceElement(TraceElement $element)
    {
        $file = $this->getFileReference($element);
        $call = $this->formatCall($element);

        return "{$file} {$call}";
    }

    /**
     * @return ValueFormatterInterface
     */
    protected function createDefaultValueFormatter()
    {
        return new ValueFormatter();
    }

    /**
     * Formats a file and line-number reference, e.g. `/path/to/file.php(123)`
     *
     * This format is identical to that of the native php stack-trace (which may be recognized by a modern IDE)
     *
     * @param TraceElement $element
     *
     * @return string
     */
    protected function getFileReference(TraceElement $element)
    {
        return $element->getFile()
            ? $element->getFile() . "(" . $element->getLine() . ")"
            : "{no file}";
    }

    /**
     * @param TraceElement $element
     *
     * @return string
     */
    protected function formatCall(TraceElement $element)
    {
        $function = $element->getClass()
            ? $element->getClass() . $element->getType() . $element->getFunction()
            : $element->getFunction();

        if ($function === 'require' || $function === 'include') {
            // bypass argument formatting for include and require statements
            $args = reset($element->getArgs()) ?: '';
        } else {
            $args = count($element->getArgs())
                ? $this->value_formatter->formatArray($element->getArgs())
                : "";
        }

        return $function
            ? "{$function}({$args})"
            : "";
    }
}
