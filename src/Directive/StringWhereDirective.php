<?php

declare(strict_types = 1);

namespace Graphpinator\Directive;

final class StringWhereDirective extends \Graphpinator\Directive\BaseWhereDirective
{
    protected const NAME = 'stringWhere';
    protected const DESCRIPTION = 'Graphpinator stringWhere directive.';

    public function __construct()
    {
        parent::__construct(
            [
                ExecutableDirectiveLocation::FIELD,
            ],
            true,
            new \Graphpinator\Argument\ArgumentSet([
                new \Graphpinator\Argument\Argument('field', \Graphpinator\Container\Container::String()),
                \Graphpinator\Argument\Argument::create('not', \Graphpinator\Container\Container::Boolean()->notNull())->setDefaultValue(false),
                new \Graphpinator\Argument\Argument('equals', \Graphpinator\Container\Container::String()),
                new \Graphpinator\Argument\Argument('contains', \Graphpinator\Container\Container::String()),
                new \Graphpinator\Argument\Argument('startsWith', \Graphpinator\Container\Container::String()),
                new \Graphpinator\Argument\Argument('endsWith', \Graphpinator\Container\Container::String()),
            ]),
            null,
            static function (\Graphpinator\Value\ListResolvedValue $value, ?string $field, bool $not, ?string $equals, ?string $contains, ?string $startsWith, ?string $endsWith) : string {
                foreach ($value as $key => $item) {
                    $singleValue = self::extractValue($item, $field, \Graphpinator\Type\Scalar\StringType::class);
                    $condition = self::satisfiesCondition($singleValue, $equals, $contains, $startsWith, $endsWith);

                    if ($condition === $not) {
                        unset($value[$key]);
                    }
                }

                return DirectiveResult::NONE;
            },
        );
    }

    private static function satisfiesCondition(string $value, ?string $equals, ?string $contains, ?string $startsWith, ?string $endsWith) : bool
    {
        if (\is_string($equals) && $value !== $equals) {
            return false;
        }

        if (\is_string($contains) && !\str_contains($value, $contains)) {
            return false;
        }

        if (\is_string($startsWith) && !\str_starts_with($value, $startsWith)) {
            return false;
        }

        if (\is_string($endsWith) && !\str_ends_with($value, $endsWith)) {
            return false;
        }

        return true;
    }

    /*private static function extractValue(\Graphpinator\Value\ResolvedValue $singleValue, ?string $where) : string
    {
        $whereArr = \is_string($where)
            ? \array_reverse(\explode('.', $where))
            : [];

        return static::extractValueImpl($singleValue, $whereArr)->getRawValue();
    }

    private static function extractValueImpl(\Graphpinator\Value\ResolvedValue $singleValue, array& $value) : \Graphpinator\Value\ResolvedValue
    {
        if (\count($value) === 0) {
            return $singleValue->getType() instanceof \Graphpinator\Type\Scalar\StringType
                ? $singleValue
                : throw new \Exception('Value has invalid type');
        }

        $where = \array_pop($value);

        if (\is_numeric($where)) {
            $where = (int) $where;

            if (!$singleValue instanceof \Graphpinator\Value\ListValue) {
                throw new \Exception('Invalid Resolved value');
            }

            if (!$singleValue->offsetExists($where)) {
                throw new \Exception('Invalid list offset');
            }

            return static::extractValueImpl($singleValue[$where], $value);
        }

        if (!$singleValue instanceof \Graphpinator\Value\TypeValue) {
            throw new \Exception('Invalid Resolved value');
        }

        if (!isset($singleValue->{$where})) {
            throw new \Exception('Invalid field offset');
        }

        return static::extractValueImpl($singleValue->{$where}->getValue(), $value);
    }*/
}
