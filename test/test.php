<?php

namespace mindplay\tracer\test;

use ErrorException;
use Exception;
use mindplay\tracer\MessageFormatter;
use mindplay\tracer\Trace;
use mindplay\tracer\TraceElement;
use mindplay\tracer\TraceFactory;
use mindplay\tracer\TraceFormatter;
use mindplay\tracer\ValueFormatter;
use RuntimeException;

require dirname(__DIR__) . "/vendor/autoload.php";

try {
    require __DIR__ . "/cases.php";
} catch (Exception $file_exception) {
    // caught!
}

//try {
//    $c = new TestClass();
//    $c->outerMethod();
//} catch (Exception $e) {
//    echo $e->__toString();
//}
//
//$c = new TestClass();
//$c->outerMethod();

if (! isset($file_exception)) {
    echo "internal error: file-level Exception was not caught";
    exit(1);
}

function exception_from_eval_method()
{
    $c = new \EvalClass();

    try {
        $c->instanceMethod();
    } catch (Exception $exception) {
        return $exception;
    }

    echo "internal error: Exception not caught";
    exit(1);
}

function exception_from_instance_method()
{
    $c = new TestClass();

    try {
        $c->instanceMethod();
    } catch (Exception $exception) {
        return $exception;
    }

    echo "internal error: Exception not caught";
    exit(1);
}

function exception_from_outer_method()
{
    $c = new TestClass();

    try {
        $c->outerMethod();
    } catch (Exception $exception) {
        return $exception;
    }

    echo "internal error: Exception not caught";
    exit(1);
}

function exception_from_error_handler()
{
    $c = new TestClass();

    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        $error = new ErrorException($errstr, 0, $errno, $errfile, $errline);

        if ($error->getSeverity() & error_reporting()) {
            throw $error;
        }
    });

    try {
        $c->divideByZero();
    } catch (Exception $exception) {
        return $exception;
    } finally {
        restore_error_handler();
    }

    echo "internal error: Exception not caught";
    exit(1);
}

function exception_from_static_method()
{
    try {
        TestClass::staticMethod();
    } catch (Exception $exception) {
        return $exception;
    }

    echo "internal error: Exception not caught";
    exit(1);
}

function exception_from_anon_func()
{
    $c = new TestClass();

    try {
        $c->anonFunction();
    } catch (Exception $exception) {
        return $exception;
    }

    echo "internal error: Exception not caught";
    exit(1);
}

function contains_parts($string, array $parts)
{
    $pattern = '/' . implode(".*", array_map("preg_quote", $parts)) . '/s';

    ok(
        preg_match($pattern, $string) === 1,
        "should contain parts: " . implode("...", $parts) . "\nactual contents:\n{$string}"
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
    "can format Exceptions",
    function () {
        $formatter = new MessageFormatter();

        contains_parts(
            $formatter->formatMessage(exception_from_instance_method()),
            [
                'Exception with message: from instance method in',
                'cases.php(21)',
            ]
        );
    }
);

test(
    "can format (backtrack through) multi-level Exceptions",
    function () {
        $formatter = new TraceFormatter();

        $factory = new TraceFactory();

        $trace = $factory->createFromException(exception_from_outer_method(), 2);

        contains_parts(
            $formatter->formatTrace($trace),
            [
                'Exception with message: from outer method',
                'cases.php(29)',
                'Exception with message: from inner method',
                'cases.php(35)',
            ]
        );
    }
);

test(
    "can format argument lists",
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

        $trace = $factory->createFromException($exception, 3);

        $formatter = new TraceFormatter();

        contains_parts(
            $formatter->formatTrace($trace),
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
    "can create elements from debug_backtrace()",
    function () {
        // TODO
    }
);

test(
    "can format debug_backtrace()",
    function () {
        $c = new TestClass();

        $backtrace = $c->outerTrace();

        $formatter = new TraceFormatter();

        $factory = new TraceFactory();

        $string = $formatter->formatTrace($factory->createFromData($backtrace));

        contains_parts(
            $string,
            [
                '#0', 'cases.php(62)', 'mindplay\tracer\test\TestClass->innerTrace()',
            ]
        );
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
        $formatter = new MessageFormatter();

        contains_parts($formatter->formatException(new Exception()), ['Exception with message: {none}']);

        contains_parts($formatter->formatException(new ErrorException("", 0, E_NOTICE)), ['ErrorException: Notice']);

        // TODO test all error-levels
    }
);

/**
 * @param Exception $exception
 *
 * @return Trace
 */
function case_trace(Exception $exception)
{
    $factory = new TraceFactory();

    return $factory
        ->createFromException($exception)
        ->filter(function (TraceElement $element) {
            return $element->getFile() === __DIR__ . DIRECTORY_SEPARATOR . 'cases.php';
        });
}

/**
 * @param Exception $exception
 *
 * @return TraceElement[]
 */
function case_elements(Exception $exception)
{
    return case_trace($exception)->getElements();
}

/**
 * @param Exception $exception
 *
 * @return TraceElement[]
 */
function all_elements(Exception $exception)
{
    $factory = new TraceFactory();

    return $factory
        ->createFromException($exception)
        ->getElements();
}

test(
    "can trace from file",
    function () use ($file_exception) {
        $elements = case_elements($file_exception);

        eq($elements[0]->getLine(), 71);

        // TODO more assertions?
    }
);

test(
    "can trace from eval()'ed code",
    function () {
        $elements = all_elements(exception_from_eval_method());

        // TODO $elements[0]->getFile() is something like:
        // "C:\workspace\test\mindplay-tracer\test\cases.php(19) : eval()'d code"
        // and $elements[0]->getLine() refers to the line-number in the code eval()'ed at that site
        // should we normalize this somehow?
    }
);

test(
    "consistency tests for debug_backtrace()",
    function () {
        // TODO
    }
);

test(
    "tests for stack-trace order for inner/outer methods",
    function () {
        // TODO
    }
);

test(
    "can trace from static methods",
    function () {
        // TODO
    }
);

test(
    "can trace from anonymous functions",
    function () {
        // TODO
    }
);

configure()->enableCodeCoverage(__DIR__ . "/build/clover.xml", dirname(__DIR__) . "/src");

exit(run());
