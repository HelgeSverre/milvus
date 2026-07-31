<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;
use HelgeSverre\Milvus\Exceptions\InvalidResponseException;

final readonly class Entity
{
    public int|string|null $id;

    public int|float|null $distance;

    public function __construct(public array $raw, string $path = 'data')
    {
        $this->id = Payload::optionalIntegerOrString($raw, 'id', $path);
        $this->distance = Payload::optionalNumber($raw, 'distance', $path);
    }

    public function field(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->raw) ? $this->raw[$name] : $default;
    }

    public function toArray(): array
    {
        return $this->raw;
    }

    public static function listFromArray(array $data): array
    {
        Payload::assertList($data, 'data');

        return array_map(
            static function (mixed $entity, int $index): self {
                if (! is_array($entity)) {
                    throw InvalidResponseException::expected("data.{$index}", 'object', $entity);
                }

                return new self($entity, "data.{$index}");
            },
            $data,
            array_keys($data),
        );
    }
}
