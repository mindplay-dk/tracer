<?php

namespace mindplay\tracer\test;

use ErrorException;
use Exception;
use mindplay\tracer\ExceptionFormatter;
use mindplay\tracer\Trace;
use mindplay\tracer\TraceElement;
use mindplay\tracer\TraceFactory;
use mindplay\tracer\TraceFormatter;
use mindplay\tracer\ValueFormatter;
use RuntimeException;

require dirname(__DIR__) . "/vendor/autoload.php";

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $error = new ErrorException($errstr, 0, $errno, $errfile, $errline);

    if ($error->getSeverity() & error_reporting()) {
        throw $error;
    }
});

try {
    require __DIR__ . "/cases.php";
} catch (Exception $file_exception) {
    // caught!
}

restore_error_handler();

if (! isset($file_exception)) {
    echo "internal error: file-level Exception was not caught";
    exit(1);
}

function contains_parts($string, array $parts)
{
    $pattern = implode(".*", array_map("preg_quote", $parts));

    ok(
        preg_match("/{$pattern}/", $string) === 1,
        "should contain text: " . implode("...", $parts),
        $string
    );
}

test(
    "can format values",
    function () {
        $unknown = fopen(__FILE__, "r");
        fclose($unknown); // closed file resources become "unknown types" in php

        $file = fopen(__FILE__, "r");

        $all_sorts = [
            true,
            false,
            null,
            123,
            0.42,
            'hello',
            [1, 2, 3],
            ["foo" => "bar", "bat" => "baz"],
            new \stdClass(),
            new TestClass(),
            $file,
            $unknown,
        ];

        $formatter = new ValueFormatter();

        $formatted = $formatter->formatValue($all_sorts);

        fclose($file);

        eq(
            $formatted,
            '[true, false, null, 123, float(0.42), "hello", [1, 2, 3], [foo => "bar", bat => "baz"], {object}, {mindplay\tracer\test\TestClass}, {stream}, {unknown type}]'
        );
    }
);

test(
    "can format argument lists",
    function () {
        $formatter = new ExceptionFormatter();

        $c = new TestClass();

        try {
            $c->outerMethod([1, 2, 3]);
        } catch (Exception $exception) {
            // caught
        }

        if (! isset($exception)) {
            echo "internal error: Exception was not caught";
            exit(1);
        }

        contains_parts(
            $formatter->formatException($exception, 3),
            ['#1', __FILE__, TestClass::class, '->outerMethod([1, 2, 3])']
        );
    }
);

test(
    "can create trace elements from data",
    function () {
        $factory = new TraceFactory();

        $trace = $factory->createFromData([
            [
                'file'     => 'foo.php',
                'line'     => 99,
                'function' => 'yada()',
                'args'     => [1, 2, 3],
                'class'    => 'Foo',
                'type'     => TraceElement::TYPE_INSTANCE,
                'object'   => new TestClass(),
            ],
        ]);

        $elements = $trace->getElements();

        eq(count($elements), 1);

        ok($elements[0] instanceof TraceElement);

        eq($elements[0]->getFile(), 'foo.php');
        eq($elements[0]->getLine(), 99);
        eq($elements[0]->getFunction(), 'yada()');
        eq($elements[0]->getArgs(), [1, 2, 3]);
        eq($elements[0]->getClass(), 'Foo');
        eq($elements[0]->getType(), TraceElement::TYPE_INSTANCE);
        ok($elements[0]->getObject() instanceof TestClass);
    }
);

test(
    "can create trace elements from Exceptions",
    function () {
        $c = new TestClass();

        try {
            $c->outerMethod([1, 2, 3]);
        } catch (Exception $exception) {
            // caught
        }

        if (! isset($exception)) {
            echo "internal error: Exception was not caught";
            exit(1);
        }

        $factory = new TraceFactory();

        $trace = $factory->createFromException($exception);

        ok($trace instanceof Trace);

        $elements = $trace->getElements();

        foreach ($elements as $element) {
            ok($element instanceof TraceElement);
        }
    }
);

test(
    "TraceElement constructor should throw",
    function () {
        expect(
            RuntimeException::class,
            "should throw for unsupported value",
            function () {
                new TraceElement(['oops' => 'nope']);
            }
        );
    }
);

test(
    "can reduce trace elements",
    function () {
        $trace = new Trace([
            new TraceElement(["file" => "foo.php"]),
            new TraceElement(["file" => "bar.php"]),
            new TraceElement(["file" => "baz.php"]),
        ]);
        
        $trace = $trace->filter(function (TraceElement $element) {
            return $element->getFile() != "bar.php";
        });

        $elements = $trace->getElements();

        eq(count($elements), 2);

        eq($elements[0]->getFile(), "foo.php");
        eq($elements[1]->getFile(), "baz.php");
    }
);

test(
    "can format severity levels",
    function () {
        $formatter = new ExceptionFormatter();

        contains_parts($formatter->formatException(new Exception()), ['Exception with message: {none}']);

        contains_parts($formatter->formatException(new ErrorException("", 0, E_NOTICE)), ['ErrorException: Notice']);

        // TODO test all error-levels
    }
);

configure()->enableCodeCoverage(__DIR__ . "/build/clover.xml", dirname(__DIR__) . "/src");

exit(run());
