<?php

declare(strict_types = 1);

namespace Graphpinator\Typesystem;

use Graphpinator\Graphpinator;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Contract\Entity;
use Graphpinator\Typesystem\Contract\EntityVisitor;
use Graphpinator\Typesystem\DirectiveUsage\DirectiveUsage;
use Graphpinator\Typesystem\DirectiveUsage\DirectiveUsageSet;
use Graphpinator\Typesystem\Exception\RootOperationTypesMustBeDifferent;
use Graphpinator\Typesystem\Exception\RootOperationTypesMustBeWithinContainer;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Location\SchemaLocation;
use Graphpinator\Typesystem\Utils\THasDirectives;
use Graphpinator\Typesystem\Utils\TOptionalDescription;

class Schema implements Entity
{
    use TOptionalDescription;
    use THasDirectives;

    public function __construct(
        private Container $container,
        private Type $query,
        private ?Type $mutation = null,
        private ?Type $subscription = null,
    )
    {
        if (Graphpinator::$validateSchema) {
            $this->validateSchema($container, $query, $mutation, $subscription);
        }

        $this->directiveUsages = new DirectiveUsageSet();
        $query->addMetaField(ResolvableField::create(
            '__schema',
            $container->getType('__Schema')->notNull(),
            function() : Schema {
                return $this;
            },
        ));
        $query->addMetaField(ResolvableField::create(
            '__type',
            $container->getType('__Type'),
            function($parent, string $name) : ?\Graphpinator\Typesystem\Contract\Type {
                return $this->container->getType($name);
            },
        )->setArguments(new ArgumentSet([
            Argument::create('name', Container::String()->notNull()),
        ])));
    }

    final public function getContainer() : Container
    {
        return $this->container;
    }

    final public function getQuery() : Type
    {
        return $this->query;
    }

    final  public function getMutation() : ?Type
    {
        return $this->mutation;
    }

    final public function getSubscription() : ?Type
    {
        return $this->subscription;
    }

    final public function accept(EntityVisitor $visitor) : mixed
    {
        return $visitor->visitSchema($this);
    }

    final public function addDirective(
        SchemaLocation $directive,
        array $arguments = [],
    ) : self
    {
        $this->directiveUsages[] = new DirectiveUsage($directive, $arguments);

        return $this;
    }

    private static function isSame(?Type $lhs, ?Type $rhs) : bool
    {
        return $lhs === $rhs
            && ($lhs !== null || $rhs !== null);
    }

    private function validateSchema(
        Container $container,
        Type $query,
        ?Type $mutation = null,
        ?Type $subscription = null,
    ) : void
    {
        if (self::isSame($query, $mutation) ||
            self::isSame($query, $subscription) ||
            self::isSame($mutation, $subscription)) {
            throw new RootOperationTypesMustBeDifferent();
        }

        if (!$container->getType($query->getName()) instanceof $query ||
            ($mutation && !$container->getType($mutation->getName()) instanceof $mutation) ||
            ($subscription && !$container->getType($subscription->getName()) instanceof $subscription)) {
            throw new RootOperationTypesMustBeWithinContainer();
        }
    }
}
