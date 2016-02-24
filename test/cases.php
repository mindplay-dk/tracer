<?php

namespace mindplay\tracer\test;

use Exception;

eval(
<<<'CODE'
class EvalClass {
    public function instanceMethod() {
        throw new Exception("from instance method");
    }

    public static function staticMethod() {
        throw new Exception("from static method");
    }
}
CODE
);

class TestClass
{
    public function outerMethod($data)
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
}

throw new Exception("from file");
