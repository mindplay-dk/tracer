<?php

namespace mindplay\tracer;

use Closure;
use ReflectionFunction;
use stdClass;

class ValueFormatter implements ValueFormatterInterface
{
    /**
     * @var int strings longer than this number of characters will be truncated in formatted strings
     */
    public $string_length = 120;

    /**
     * @inheritdoc
     */
    public function formatArray(array $array)
    {
        $formatted = array_map([$this, "formatValue"], $array);

        if (array_keys($array) !== range(0, count($array) - 1)) {
            foreach ($formatted as $name => $value) {
                $formatted[$name] = "{$name} => {$value}";
            }
        }

        return implode(", ", $formatted);
    }

    /**
     * @inheritdoc
     */
    public function formatValue($value)
    {
        $type = is_callable($value)
            ? "callable"
            : strtolower(gettype($value));
        
        switch ($type) {
            case "boolean":
                return $value ? "true" : "false";

            case "integer":
                return number_format($value, 0, "", "");

            case "double": // (for historical reasons "double" is returned in case of a float, and not simply "float")
                $formatted = sprintf("%.6g", $value);

                return $value == $formatted
                    ? "float({$formatted})"
                    : "float(~{$formatted})";

            case "string":
                $string = strlen($value) > $this->string_length
                    ? substr($value, 0, $this->string_length) . "...[" . strlen($value) . "]"
                    : $value;

                return "\"{$string}\"";

            case "array":
                return "[" . $this->formatArray($value) . "]";

            case "object":
                return "{" . ($value instanceof stdClass ? "object" : get_class($value)) . "}";

            case "resource":
                return "{" . get_resource_type($value) . "}";
            
            case "callable":
                if (is_string($value)) {
                    return "{$value}()";
                } else if (is_array($value)) {
                    return is_object($value[0])
                        ? '{' . get_class($value[0]) . "}->{$value[1]}()"
                        : "{$value[0]}::{$value[1]}()";
                } else if ($value instanceof Closure) {
                    $reflection = new ReflectionFunction($value);

                    return "closure in " . $reflection->getFileName() . "({$reflection->getStartLine()})";
                } else if (method_exists($value, "__invoke")) {
                    return get_class($value) . "::__invoke()";
                }

                return "unknown callable";
            
            case "null":
                return "null";
        }

        return "{{$type}}"; // "unknown type" and possibly unsupported (future) types
    }
}
