<?php

declare(strict_types = 1);

namespace Graphpinator;

final class Result implements \JsonSerializable
{
    use \Nette\SmartObject;

    public function __construct(
        private ?\Graphpinator\Value\TypeValue $data = null,
        private ?array $errors = null,
    )
    {
    }

    public function getData() : ?\Graphpinator\Value\TypeValue
    {
        return $this->data;
    }

    public function getErrors() : ?array
    {
        return $this->errors;
    }

    public function jsonSerialize() : \stdClass
    {
        $return = new \stdClass();

        if ($this->data instanceof \Graphpinator\Value\TypeValue) {
            $return->data = $this->data;
        }

        if (\is_array($this->errors)) {
            $return->errors = $this->errors;
        }

        return $return;
    }

    public function toString() : string
    {
        return \Infinityloop\Utils\Json\MapJson::fromNative($this->jsonSerialize())->toString();
    }
}
