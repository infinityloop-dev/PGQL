<?php

declare(strict_types = 1);

namespace Graphpinator\Typesystem\Exception;

final class RootOperationTypesMustBeWithinContainer extends TypeError
{
    public const MESSAGE = 'Root operation types must be within container.';
}
