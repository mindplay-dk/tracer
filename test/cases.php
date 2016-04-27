<?php

namespace mindplay\tracer\test;

use Exception;

eval(
<<<'CODE'
class EvalClass {
    public function instanceMethod() {
        throw new Exception("from eval instance method");
    }
}
CODE
);

class InvokableTestClass
{
    public function __invoke()
    {}
}

class TestClass
{
    public function instanceMethod()
    {
        throw new Exception("from instance method");
    }

    public function outerMethod($data = null)
    {
        try {
            $this->innerMethod();
        } catch (Exception $e) {
            throw new Exception("from outer method", 123, $e);
        }
    }

    private function innerMethod()
    {
        throw new Exception("from inner method");
    }

    public function divideByZero()
    {
        return 1/0;
    }

    public static function staticMethod()
    {
        throw new Exception("from static method");
    }

    public function anonFunction()
    {
        $anon = function () {
            throw new Exception("from anonymous function");
        };

        $anon();
    }

    /**
     * @return array
     */
    public function outerTrace()
    {
        return $this->innerTrace();
    }

    public function innerTrace()
    {
        return debug_backtrace();
    }
}

throw new Exception("from file");
