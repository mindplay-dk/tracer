<?php

namespace mindplay\tracer;

/**
 * This interface defines the API for formatting of individual or multiple values as strings.
 *
 * @see ValueFormatter
 */
interface ValueFormatterInterface
{
    /**
     * Format a array of values as a human-readable, comma-separated string.
     *
     * Array keys will be included only if the array has non-numeric (or non-sequential) keys.
     *
     * @param array $array
     *
     * @return string
     */
    public function formatArray(array $array);

    /**
     * Format any value as a human-readable string.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function formatValue($value);
}
