<?php

declare(strict_types = 1);

namespace Graphpinator\Tests\Feature;

use \Graphpinator\Typesystem\Argument\Argument;
use \Graphpinator\Typesystem\Argument\ArgumentSet;
use \Graphpinator\Typesystem\Container;
use \Graphpinator\Typesystem\Field\ResolvableField;
use \Graphpinator\Typesystem\Field\ResolvableFieldSet;

final class InputTypeCoercionTest extends \PHPUnit\Framework\TestCase
{
    public static function getSimpleInput() : \Graphpinator\Typesystem\InputType
    {
        return new class extends \Graphpinator\Typesystem\InputType
        {
            protected const NAME = 'SimpleInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new \Graphpinator\Typesystem\Argument\ArgumentSet([
                    new Argument(
                        'string',
                        Container::String(),
                    ),
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'stringNotNull',
                        \Graphpinator\Typesystem\Container::String()->notNull(),
                    ),
                ]);
            }
        };
    }

    public function inputObjectDataProvider() : array
    {
        return [
            [
                'query queryName { field1(arg: {stringNotNull: "value"}) }',
                'missing value',
            ],
            [
                'query queryName { field1(arg: {string: "optional", stringNotNull: "value"}) }',
                'optional value',
            ],
            [
                'query queryName { field1(arg: {string: null, stringNotNull: "value"}) }',
                ' value',
            ],
        ];
    }

    /**
     * @dataProvider inputObjectDataProvider
     */
    public function testInputObject(string $query, string $expected) : void
    {
        $request = \Infinityloop\Utils\Json::fromNative((object) [
            'query' => $query,
        ]);
        $expected = \Infinityloop\Utils\Json::fromNative((object) [
            'data' => [
                'field1' => $expected,
            ],
        ]);

        $result = self::getGraphpinator()->run(new \Graphpinator\Request\JsonRequestFactory($request));
        self::assertSame($expected->toString(), $result->toString());
    }

    protected static function getGraphpinator() : \Graphpinator\Graphpinator
    {
        $query = new class () extends \Graphpinator\Typesystem\Type
        {
            protected const NAME = 'Query';

            public function __construct()
            {
                parent::__construct();
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'field1',
                        \Graphpinator\Typesystem\Container::String(),
                        static function($parent, \stdClass $arg) : string {
                            $first = \property_exists($arg, 'string')
                                ? $arg->string
                                : 'missing';

                            return $first . ' ' . $arg->stringNotNull;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        new \Graphpinator\Typesystem\Argument\Argument(
                            'arg',
                            InputTypeCoercionTest::getSimpleInput(),
                        ),
                    ])),
                ]);
            }
        };

        return new \Graphpinator\Graphpinator(
            new \Graphpinator\Typesystem\Schema(
                new \Graphpinator\SimpleContainer(['Query' => $query], []),
                $query,
            ),
        );
    }
}
