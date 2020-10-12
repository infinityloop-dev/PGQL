<?php

declare(strict_types = 1);

namespace Graphpinator\Value;

use \Graphpinator\Argument\ArgumentValue;

final class InputValue implements \Graphpinator\Value\InputedValue
{
    use \Nette\SmartObject;

    private \Graphpinator\Type\InputType $type;
    private \stdClass $value;

    public function __construct(\Graphpinator\Type\InputType $type, \stdClass $rawValue)
    {
        $rawValue = self::merge($rawValue, (object) $type->getArguments()->getRawDefaults());

        foreach ($rawValue as $name => $temp) {
            if (isset($type->getArguments()[$name])) {
                continue;
            }

            throw new \Exception('Unknown field for input value');
        }

        $value = new \stdClass();

        foreach ($type->getArguments() as $argument) {
            $value->{$argument->getName()} = \Graphpinator\Argument\ArgumentValue::fromRaw($argument, $rawValue->{$argument->getName()}
                ?? null);
        }

        $this->type = $type;
        $this->value = $value;

        $type->validateConstraints($this);
    }

    public function getRawValue() : \stdClass
    {
        $return = new \stdClass();

        foreach ($this->value as $fieldName => $fieldValue) {
            \assert($fieldValue instanceof ArgumentValue);

            $return->{$fieldName} = $fieldValue->getValue()->getRawValue();
        }

        return $return;
    }

    public function getType() : \Graphpinator\Type\InputType
    {
        return $this->type;
    }

    public function printValue(bool $prettyPrint = false, int $indentLevel = 1) : string
    {
        if ($prettyPrint) {
            return $this->prettyPrint($indentLevel);
        }

        $component = [];

        foreach ($this->value as $key => $value) {
            \assert($value instanceof ArgumentValue);

            $component[] = $key . ':' . $value->getValue()->printValue();
        }

        return '{' . \implode(',', $component) . '}';
    }

    private static function merge(\stdClass $core, \stdClass $supplement) : \stdClass
    {
        foreach ($supplement as $key => $value) {
            if (\property_exists($core, $key)) {
                if ($core->{$key} instanceof \stdClass &&
                    $supplement->{$key} instanceof \stdClass) {
                    $core->{$key} = self::merge($core->{$key}, $supplement->{$key});
                }

                continue;
            }

            $core->{$key} = $value;
        }

        return $core;
    }

    private function prettyPrint(int $indentLevel) : string
    {
        if (\count((array) $this->value) === 0) {
            return '{}';
        }

        $component = [];
        $indent = \str_repeat('  ', $indentLevel);
        $innerIndent = $indent . '  ';

        foreach ($this->value as $key => $value) {
            \assert($value instanceof ArgumentValue);

            $component[] = $key . ': ' . $value->getValue()->printValue(true, $indentLevel + 1);
        }

        return '{' . \PHP_EOL . $innerIndent . \implode(',' . \PHP_EOL . $innerIndent, $component) . \PHP_EOL . $indent . '}';
    }

    public function __isset(string $offset) : bool
    {
        return \property_exists($this->value, $offset);
    }

    public function __get(string $offset) : ArgumentValue
    {
        return $this->value->{$offset};
    }

    public function __set(string $offset, ArgumentValue $value) : void
    {
        if ($value->getArgument() !== $this->type->getArguments()[$offset]) {
            throw new \Exception();
        }

        $this->value->{$offset} = $value;
    }
}
