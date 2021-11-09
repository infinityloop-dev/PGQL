<?php

declare(strict_types = 1);

namespace Graphpinator\Tests\Spec;

use \Graphpinator\Exception\Value\ValueCannotBeNull;
use \Graphpinator\Normalizer\Exception\UnknownVariable;
use \Graphpinator\Normalizer\Exception\VariableTypeInputable;
use \Graphpinator\Request\Exception\VariablesNotObject;
use \Infinityloop\Utils\Json;

final class VariableTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int) { fieldAbc { fieldXyz(arg1: $var1) { name } } }',
                    'variables' => (object) ['var1' => 456],
                ]),
                Json::fromNative((object) ['data' => ['fieldAbc' => ['fieldXyz' => ['name' => 'Test 456']]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int) { fieldAbc { fieldXyz(arg1: $var1) { name } } }',
                    'variables' => (object) ['var1' => 123],
                ]),
                Json::fromNative((object) ['data' => ['fieldAbc' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = 456) { fieldAbc { fieldXyz(arg1: $var1) { name } } }',
                    'variables' => (object) [],
                ]),
                Json::fromNative((object) ['data' => ['fieldAbc' => ['fieldXyz' => ['name' => 'Test 456']]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = 123) { fieldAbc { fieldXyz(arg1: $var1) { name } } }',
                    'variables' => (object) [],
                ]),
                Json::fromNative((object) ['data' => ['fieldAbc' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
        ];
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Infinityloop\Utils\Json $request
     * @param \Infinityloop\Utils\Json $expected
     */
    public function testSimple(Json $request, Json $expected) : void
    {
        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        self::assertSame($expected->toString(), $result->toString());
    }

    public function invalidDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = "123") { fieldAbc { fieldXyz { name } } }',
                    'variables' => (object) [],
                ]),
                \Graphpinator\Exception\Value\InvalidValue::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = "123") { fieldAbc { fieldXyz { name } } }',
                    'variables' => (object) [],
                ]),
                \Graphpinator\Exception\Value\InvalidValue::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = "123") { fieldAbc { fieldXyz { name } } }',
                    'variables' => ['var1' => '123'],
                ]),
                VariablesNotObject::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int!) { fieldAbc { fieldXyz { name } } }',
                    'variables' => (object) [],
                ]),
                ValueCannotBeNull::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Abc) { fieldAbc { fieldXyz { name } } }',
                    'variables' => (object) [],
                ]),
                VariableTypeInputable::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Abc!) { fieldAbc { fieldXyz { name } } }',
                    'variables' => (object) [],
                ]),
                VariableTypeInputable::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldAbc { fieldXyz(arg1: $varNonExistent) { name } } }',
                    'variables' => (object) [],
                ]),
                UnknownVariable::class,
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     * @param \Infinityloop\Utils\Json $request
     */
    public function testInvalid(Json $request, string $exception) : void
    {
        $this->expectException($exception);

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }
}
