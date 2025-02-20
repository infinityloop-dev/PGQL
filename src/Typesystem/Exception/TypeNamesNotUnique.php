<?php

declare(strict_types = 1);

namespace Graphpinator\Typesystem\Exception;

final class TypeNamesNotUnique extends TypeError
{
    public const MESSAGE = 'Type names are not unique.';
}
