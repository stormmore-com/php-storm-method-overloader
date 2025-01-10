<?php

namespace Storm\tests;

require_once "misc/UserClass.php";
require_once "misc/UserClass2.php";

use PHPUnit\Framework\TestCase;
use Storm\MethodOverload\MethodOverloader;
use stdClass;
use Storm\tests\misc\UserClass;
use Storm\tests\misc\UserClass2;
use InvalidArgumentException;

final class MethodOverloadTest extends TestCase
{
    private MethodOverloader $methodOverload;

    public function testWhenUserClassNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MethodOverloader::create()->register(function() {}, 'UserClass5');
    }

    public function testUserCallbackOnNotExistingMethodOverload(): void
    {
        $result = MethodOverloader::create()
            ->onFailure(function () { return 0; })
            ->invoke(['none', 'int']);

        $this->assertEquals(0, $result);
    }

    public function test4ArgsNotFoundCallableMethodOverload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->methodOverload->invoke([null, null, null, null]);
    }

    public function testStringIntStringNotFoundCallableMethodOverload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->methodOverload->invoke(['arg1', 5, "arg2"]);
    }

    public function testStringMethodOverload(): void
    {
        $result = $this->methodOverload->invoke(['arg1']);

        $this->assertEquals('_string_fun_arg1', $result);
    }

    public function testIntMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([5]);

        $this->assertEquals('_int_fun_5', $result);
    }

    public function testFloatMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([5.5]);

        $this->assertEquals('_float_fun_5.5', $result);
    }

    public function testNumericStringMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([5.7, "string"]);

        $this->assertEquals('_numeric_string_fun_5.7_string', $result);
    }

    public function testBoolTrueMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([true]);

        $this->assertEquals('_bool_fun_true', $result);
    }

    public function testBoolFalseMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([false]);

        $this->assertEquals('_bool_fun_false', $result);
    }

    public function testArrayMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([[4, 7]]);

        $this->assertEquals('_arr_fun_4,7', $result);
    }

    public function testObjectMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([new stdClass()]);

        $this->assertEquals('_obj_fun_stdClass', $result);
    }

    public function testCallableMethodOverload(): void
    {
        $callable = function () {
            return "inline_fun";
        };
        $result = $this->methodOverload->invoke([$callable]);

        $this->assertEquals('_callable_inline_fun', $result);
    }

    public function testResourceMethodOverload(): void
    {
        $resource = fopen("README.md", "r");
        $result = $this->methodOverload->invoke([$resource]);

        $this->assertEquals('_resource', $result);
    }

    public function test2ArgsStringOverload(): void
    {
        $result = $this->methodOverload->invoke(["string1", "string2"]);

        $this->assertEquals('_string_string_string1_string2', $result);
    }

    public function test3ArgsStringOverload(): void
    {
        $result = $this->methodOverload->invoke(["string1", "string2", "string3"]);

        $this->assertEquals('_string_string_string_string1_string2_string3', $result);
    }

    public function test2ArgsIntOverload(): void
    {
        $result = $this->methodOverload->invoke([5, 7]);

        $this->assertEquals('_int_int_5_7', $result);
    }

    public function test2ArgsFloatOverload(): void
    {
        $result = $this->methodOverload->invoke([5.5, 4.4]);

        $this->assertEquals('_float_float_5.5_4.4', $result);
    }

    public function test3ArgsIntOverload(): void
    {
        $result = $this->methodOverload->invoke([1, 2, 3]);

        $this->assertEquals('_int_int_int_1_2_3', $result);
    }

    public function testUserClassArgMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([new UserClass()]);

        $this->assertEquals('UserClass', $result);
    }

    public function test2ArgsUserClassMethodOverload(): void
    {
        $result = $this->methodOverload->invoke([new UserClass(), new UserClass2()]);

        $this->assertEquals('UserClass_UserClass2', $result);
    }

    public function testNullOverload(): void
    {
        $result = $this->methodOverload->invoke([null]);

        $this->assertEquals("_string_fun_", $result);
    }

    public function test2NullOverload(): void
    {
        $result = $this->methodOverload->invoke([null, null]);

        $this->assertEquals("_numeric_string_fun__", $result);
    }

    public function test3NullOverload(): void
    {
        $result = $this->methodOverload->invoke([null, null, null]);

        $this->assertEquals("_string_string_string___", $result);
    }


    public function testPrivateMethodOverload(): void
    {
        $this->methodOverload = (new MethodOverloader())
            ->register($this->hello(...), 'string');
        $hello = $this->methodOverload->invoke(['John']);

        $this->assertEquals('Hello John', $hello);
    }

    public function testStaticMethodOverload(): void
    {
        $this->methodOverload = (new MethodOverloader())
            ->register(self::staticHello(...), 'string');
        $hello = $this->methodOverload->invoke(['John']);

        $this->assertEquals('Hello John', $hello);
    }

    private function hello(string $name): string
    {
        return 'Hello ' . $name;
    }

    private static function staticHello(string $name): string
    {
        return 'Hello ' . $name;
    }

    public function setUp(): void
    {
        $this->methodOverload = (new MethodOverloader())
            ->register(function ($string) {
                return "_string_fun_" . $string;
            }, 'string')
            ->register(function ($int) {
                return "_int_fun_" . $int;
            }, 'int')
            ->register(function ($float) {
                return "_float_fun_" . $float;
            }, 'float')
            ->register(function ($numeric, $string) {
                return "_numeric_string_fun_" . $numeric . "_" . $string;
            }, 'numeric', 'string')
            ->register(function (bool $bool) {
                return "_bool_fun_" . ($bool ? 'true' : 'false');
            }, "bool")
            ->register(function ($array) {
                return "_arr_fun_" . implode(',', $array);
            }, 'array')
            ->register(function ($object) {
                return "_obj_fun_" . get_class($object);
            }, 'object')
            ->register(function ($callable) {
                return "_callable_" . $callable();
            }, 'callable')
            ->register(function ($resource) {
                return "_resource";
            }, 'resource')
            ->register(function ($mixed) {
                return "_mixed_" . $mixed;
            }, "mixed")
            ->register(function ($val1, $val2) {
                return "_string_string_{$val1}_{$val2}";
            }, 'string', 'string')
            ->register(function ($val1, $val2, $val3) {
                return "_string_string_string_{$val1}_{$val2}_{$val3}";
            }, 'string', 'string', 'string')
            ->register(function ($val1, $val2) {
                return "_int_int_{$val1}_{$val2}";
            }, 'int', 'int')
            ->register(function ($val, $val2) {
                return "_float_float_{$val}_{$val2}";
            }, "float", "float")
            ->register(function ($val1, $val2, $val3) {
                return "_int_int_int_{$val1}_{$val2}_{$val3}";
            }, 'int', 'int', 'int')
            ->register(function ($obj) {
                return 'UserClass';
            }, UserClass::class)
            ->register(function ($obj) {
                return 'UserClass_UserClass2';
            }, UserClass::class, UserClass2::class);
    }
}