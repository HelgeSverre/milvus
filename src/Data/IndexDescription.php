<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class IndexDescription
{
    public function __construct(
        public string $indexName,
        public string $fieldName,
        public ?string $metricType,
        public array $raw,
    ) {}

    public static function fromArray(array $data, string $path = 'data.indexes'): self
    {
        return new self(
            indexName: Payload::requiredString($data, 'indexName', $path),
            fieldName: Payload::requiredString($data, 'fieldName', $path),
            metricType: Payload::optionalString($data, 'metricType', $path),
            raw: $data,
        );
    }
}
