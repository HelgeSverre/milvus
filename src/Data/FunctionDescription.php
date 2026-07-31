<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class FunctionDescription
{
    public function __construct(
        public string $name,
        public string $type,
        public ?string $description,
        public int|string|null $id,
        public array $inputFieldNames,
        public array $outputFieldNames,
        public array $params,
        public array $raw,
    ) {}

    public static function fromArray(array $data, string $path = 'data.functions'): self
    {
        return new self(
            name: Payload::requiredString($data, 'name', $path),
            type: Payload::requiredString($data, 'type', $path),
            description: Payload::optionalString($data, 'description', $path),
            id: Payload::optionalIntegerOrString($data, 'id', $path),
            inputFieldNames: Payload::listOfStrings($data, 'inputFieldNames', $path),
            outputFieldNames: Payload::listOfStrings($data, 'outputFieldNames', $path),
            params: Payload::optionalArray($data, 'params', $path),
            raw: $data,
        );
    }
}
