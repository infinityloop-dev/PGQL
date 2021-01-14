<?php

declare(strict_types = 1);

namespace Graphpinator\Directive;

final class FloatWhereDirective extends \Graphpinator\Directive\BaseWhereDirective
{
    protected const NAME = 'floatWhere';
    protected const DESCRIPTION = 'Graphpinator floatWhere directive.';
    protected const TYPE = \Graphpinator\Type\Scalar\FloatType::class;
    protected const TYPE_NAME = 'Float';

    public function __construct()
    {
        parent::__construct(
            [
                ExecutableDirectiveLocation::FIELD,
            ],
            true,
            new \Graphpinator\Argument\ArgumentSet([
                new \Graphpinator\Argument\Argument('field', \Graphpinator\Container\Container::String()),
                \Graphpinator\Argument\Argument::create('not', \Graphpinator\Container\Container::Boolean()->notNull())
                    ->setDefaultValue(false),
                new \Graphpinator\Argument\Argument('equals', \Graphpinator\Container\Container::Float()),
                new \Graphpinator\Argument\Argument('greaterThan', \Graphpinator\Container\Container::Float()),
                new \Graphpinator\Argument\Argument('lessThan', \Graphpinator\Container\Container::Float()),
                \Graphpinator\Argument\Argument::create('orNull', \Graphpinator\Container\Container::Boolean()->notNull())
                    ->setDefaultValue(false),
            ]),
            null,
            static function (
                \Graphpinator\Value\ListResolvedValue $value,
                ?string $field,
                bool $not,
                ?float $equals,
                ?float $greaterThan,
                ?float $lessThan,
                bool $orNull,
            ) : string {
                foreach ($value as $key => $item) {
                    $singleValue = self::extractValue($item, $field);
                    $condition = self::satisfiesCondition($singleValue, $equals, $greaterThan, $lessThan, $orNull);

                    if ($condition === $not) {
                        unset($value[$key]);
                    }
                }

                return DirectiveResult::NONE;
            },
        );
    }

    public function validateType(\Graphpinator\Type\Contract\Definition $type) : bool
    {
        return $type instanceof \Graphpinator\Type\ListType
            && $type->getInnerType() instanceof \Graphpinator\Type\Scalar\FloatType;
    }

    private static function satisfiesCondition(?float $value, ?float $equals, ?float $greaterThan, ?float $lessThan, bool $orNull) : bool
    {
        if ($value === null) {
            return $orNull === true;
        }

        if (\is_float($equals) && $value !== $equals) {
            return false;
        }

        if (\is_float($greaterThan) && $value < $greaterThan) {
            return false;
        }

        if (\is_float($lessThan) && $value > $lessThan) {
            return false;
        }

        return true;
    }
}
