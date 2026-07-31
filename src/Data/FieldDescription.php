<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class FieldDescription
{
    public function __construct(
        public string $name,
        public string $type,
        public int|string|null $id,
        public ?bool $primaryKey,
        public ?bool $partitionKey,
        public ?bool $clusteringKey,
        public ?bool $autoId,
        public ?string $description,
        public ?bool $nullable,
        public ?string $defaultValue,
        public ?bool $isFunctionOutput,
        public array $params,
        public ?string $elementType,
        public array $raw,
    ) {}

    public static function fromArray(array $data, string $path = 'data.fields'): self
    {
        return new self(
            name: Payload::requiredString($data, 'name', $path),
            type: Payload::requiredString($data, 'type', $path),
            id: Payload::optionalIntegerOrString($data, 'id', $path),
            primaryKey: Payload::optionalBoolean($data, 'primaryKey', $path),
            partitionKey: Payload::optionalBoolean($data, 'partitionKey', $path),
            clusteringKey: Payload::optionalBoolean($data, 'clusteringKey', $path),
            autoId: Payload::optionalBoolean($data, 'autoId', $path),
            description: Payload::optionalString($data, 'description', $path),
            nullable: Payload::optionalBoolean($data, 'nullable', $path),
            defaultValue: Payload::optionalString($data, 'defaultValue', $path),
            isFunctionOutput: Payload::optionalBoolean($data, 'isFunctionOutput', $path),
            params: Payload::optionalArray($data, 'params', $path),
            elementType: Payload::optionalString($data, 'elementType', $path),
            raw: $data,
        );
    }
}
