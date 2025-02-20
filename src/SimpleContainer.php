<?php

declare(strict_types = 1);

namespace Graphpinator;

use Graphpinator\Introspection\Directive;
use Graphpinator\Introspection\DirectiveLocation;
use Graphpinator\Introspection\EnumValue;
use Graphpinator\Introspection\Field;
use Graphpinator\Introspection\InputValue;
use Graphpinator\Introspection\Schema;
use Graphpinator\Introspection\Type;
use Graphpinator\Introspection\TypeKind;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Contract\NamedType;
use Graphpinator\Typesystem\Directive as TypesystemDirective;
use Graphpinator\Typesystem\Exception\TypeNamesNotUnique;

/**
 * Simple Container implementation
 */
class SimpleContainer extends Container
{
    protected array $types = [];
    protected array $directives = [];
    protected array $combinedTypes = [];
    protected array $combinedDirectives = [];

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param iterable<NamedType> $types
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param iterable<Directive> $directives
     */
    public function __construct(
        iterable $types,
        iterable $directives,
    )
    {
        self::$builtInTypes = [
            'ID' => self::ID(),
            'Int' => self::Int(),
            'Float' => self::Float(),
            'String' => self::String(),
            'Boolean' => self::Boolean(),
            '__Schema' => new Schema($this),
            '__Type' => new Type($this),
            '__TypeKind' => new TypeKind(),
            '__Field' => new Field($this),
            '__EnumValue' => new EnumValue(),
            '__InputValue' => new InputValue($this),
            '__Directive' => new Directive($this),
            '__DirectiveLocation' => new DirectiveLocation(),
        ];
        self::$builtInDirectives = [
            'skip' => self::directiveSkip(),
            'include' => self::directiveInclude(),
            'deprecated' => self::directiveDeprecated(),
            'specifiedBy' => self::directiveSpecifiedBy(),
            'oneOf' => self::directiveOneOf(),
        ];

        $typeCount = 0;

        foreach ($types as $type) {
            $this->types[$type->getName()] = $type;
            $typeCount++;
        }

        $directivesCount = 0;

        foreach ($directives as $directive) {
            $this->directives[$directive->getName()] = $directive;
            $directivesCount++;
        }

        if (Graphpinator::$validateSchema &&
            ($typeCount !== \count($this->types) ||
            $directivesCount !== \count($this->directives))) {
            throw new TypeNamesNotUnique();
        }

        $this->combinedTypes = \array_merge($this->types, self::$builtInTypes);
        $this->combinedDirectives = \array_merge($this->directives, self::$builtInDirectives);
    }

    public function getType(string $name) : ?NamedType
    {
        return $this->combinedTypes[$name]
            ?? null;
    }

    public function getTypes(bool $includeBuiltIn = false) : array
    {
        return $includeBuiltIn
            ? $this->combinedTypes
            : $this->types;
    }

    public function getDirective(string $name) : ?TypesystemDirective
    {
        return $this->combinedDirectives[$name]
            ?? null;
    }

    public function getDirectives(bool $includeBuiltIn = false) : array
    {
        return $includeBuiltIn
            ? $this->combinedDirectives
            : $this->directives;
    }
}
