<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class DatabaseDescription
{
    public function __construct(
        public string $dbName,
        public int|string|null $dbId,
        public array $properties,
        public array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            dbName: Payload::requiredString($data, 'dbName', 'data'),
            dbId: Payload::optionalIntegerOrString($data, 'dbID', 'data'),
            properties: self::properties($data),
            raw: $data,
        );
    }

    private static function properties(array $data): array
    {
        $properties = Payload::listOfArrays($data, 'properties', 'data');

        return array_map(
            static fn (array $property, int $index): DatabaseProperty => DatabaseProperty::fromArray(
                $property,
                "data.properties.{$index}",
            ),
            $properties,
            array_keys($properties),
        );
    }
}
