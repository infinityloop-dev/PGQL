<?php

declare(strict_types = 1);

namespace Graphpinator\Tests\Unit\Value;

final class IsSameTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [
                new \Graphpinator\Value\VariableValue(
                    new \Graphpinator\Type\Spec\StringType(),
                    new \Graphpinator\Normalizer\Variable\Variable('var1', new \Graphpinator\Type\Spec\StringType(), null),
                ),
                new \Graphpinator\Value\VariableValue(
                    new \Graphpinator\Type\Spec\StringType(),
                    new \Graphpinator\Normalizer\Variable\Variable('var1', new \Graphpinator\Type\Spec\StringType(), null),
                ),
                true,
            ],
            [
                new \Graphpinator\Value\VariableValue(
                    new \Graphpinator\Type\Spec\StringType(),
                    new \Graphpinator\Normalizer\Variable\Variable('var1', new \Graphpinator\Type\Spec\StringType(), null),
                ),
                new \Graphpinator\Value\VariableValue(
                    new \Graphpinator\Type\Spec\StringType(),
                    new \Graphpinator\Normalizer\Variable\Variable('var2', new \Graphpinator\Type\Spec\StringType(), null),
                ),
                false,
            ],
            [
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [],
                        ],
                    )
                ),
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [],
                        ],
                    )
                ),
                true,
            ],
            [
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [],
                        ],
                    )
                ),
                new \Graphpinator\Value\VariableValue(
                    new \Graphpinator\Type\Spec\StringType(),
                    new \Graphpinator\Normalizer\Variable\Variable('var2', new \Graphpinator\Type\Spec\StringType(), null),
                ),
                false,
            ],
            [
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [],
                        ],
                    )
                ),
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [],
                            'bool' => true,
                        ],
                    )
                ),
                false,
            ],
            [
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [],
                        ],
                    )
                ),
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [1],
                        ],
                    )
                ),
                false,
            ],
            [
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [1],
                        ],
                    )
                ),
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [1, 2],
                        ],
                    )
                ),
                false,
            ],
            [
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [1, 2],
                        ],
                    )
                ),
                \Graphpinator\Tests\Spec\TestSchema::getSimpleInput()->accept(
                    new \Graphpinator\Value\ConvertRawValueVisitor(
                        (object) [
                            'name' => 'test',
                            'number' => [2, 1],
                        ],
                    )
                ),
                false,
            ],
        ];
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Value\InputedValue $lhs
     * @param \Graphpinator\Value\InputedValue $rhs
     * @param bool $result
     */
    public function testSimple(\Graphpinator\Value\InputedValue $lhs, \Graphpinator\Value\InputedValue $rhs, bool $result) : void
    {
        self::assertSame($result, $lhs->isSame($rhs));
    }
}
