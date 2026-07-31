<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class DatabaseProperty
{
    public function __construct(
        public string $key,
        public string $value,
        public array $raw,
    ) {}

    public static function fromArray(array $data, string $path = 'data.properties'): self
    {
        return new self(
            key: Payload::requiredString($data, 'key', $path),
            value: Payload::requiredString($data, 'value', $path),
            raw: $data,
        );
    }
}
