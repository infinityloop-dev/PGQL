<?php

declare(strict_types = 1);

namespace PGQL\Type;

final class ListType extends \PGQL\Type\Contract\ModifierDefinition
{
    public function createValue($rawValue) : \PGQL\Value\ValidatedValue
    {
        return \PGQL\Value\ListValue::create($rawValue, $this);
    }

    public function resolveFields(?\PGQL\Parser\RequestFieldSet $requestedFields, \PGQL\Field\ResolveResult $parent) : array
    {
        if ($requestedFields === null) {
            throw new \Exception('List without fields specified.');
        }

        if (!$parent->getResult() instanceof \PGQL\Value\ListValue) {
            throw new \Exception('Cannot create list');
        }

        $return = [];

        foreach ($parent->getResult() as $val) {
            $return[] = $this->innerType->resolveFields($requestedFields, \PGQL\Field\ResolveResult::fromValidated($val));
        }

        return $return;
    }

    public function validateValue($rawValue) : void
    {
        if ($rawValue === null || $rawValue instanceof \PGQL\Value\ValidatedValue) {
            return;
        }

        if (!\is_iterable($rawValue)) {
            throw new \Exception('Value must be list or null.');
        }

        foreach ($rawValue as $val) {
            $this->innerType->validateValue($val);
        }
    }

    public function applyDefaults($value)
    {
        if ($value === null || !$this->innerType instanceof \PGQL\Type\Contract\Inputable) {
            return $value;
        }

        if (!\is_iterable($value)) {
            throw new \Exception('Value has to be list.');
        }

        $return = [];

        foreach ($value as $val) {
            $return[] = $this->innerType->applyDefaults($val);
        }

        return $return;
    }

    public function isInstanceOf(\PGQL\Type\Contract\Definition $type): bool
    {
        if ($type instanceof self) {
            return $this->innerType->isInstanceOf($type->getInnerType());
        }

        if ($type instanceof NotNullType) {
            return $this->isInstanceOf($type->getInnerType());
        }

        return false;
    }

    public function notNull() : \PGQL\Type\NotNullType
    {
        return new \PGQL\Type\NotNullType($this);
    }
}
