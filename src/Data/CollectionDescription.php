<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class CollectionDescription
{
    public function __construct(
        public string $collectionName,
        public array $aliases,
        public ?bool $autoId,
        public int|string|null $collectionId,
        public ?string $consistencyLevel,
        public ?string $description,
        public ?bool $enableDynamicField,
        public array $fields,
        public array $functions,
        public array $indexes,
        public ?string $load,
        public ?int $partitionsNum,
        public array $properties,
        public ?int $shardsNum,
        public ?string $externalSource,
        public ?string $externalSpec,
        public array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        $autoId = array_key_exists('autoID', $data)
            ? Payload::optionalBoolean($data, 'autoID', 'data')
            : Payload::optionalBoolean($data, 'autoId', 'data');

        $partitionsNum = array_key_exists('partitionsNum', $data)
            ? Payload::optionalInteger($data, 'partitionsNum', 'data')
            : Payload::optionalInteger($data, 'partitionNum', 'data');

        return new self(
            collectionName: Payload::requiredString($data, 'collectionName', 'data'),
            aliases: Payload::listOfStrings($data, 'aliases', 'data'),
            autoId: $autoId,
            collectionId: Payload::optionalIntegerOrString($data, 'collectionID', 'data'),
            consistencyLevel: Payload::optionalString($data, 'consistencyLevel', 'data'),
            description: Payload::optionalString($data, 'description', 'data'),
            enableDynamicField: Payload::optionalBoolean($data, 'enableDynamicField', 'data'),
            fields: self::fields($data),
            functions: self::functions($data),
            indexes: self::indexes($data),
            load: Payload::optionalString($data, 'load', 'data'),
            partitionsNum: $partitionsNum,
            properties: self::properties($data),
            shardsNum: Payload::optionalInteger($data, 'shardsNum', 'data'),
            externalSource: Payload::optionalString($data, 'externalSource', 'data'),
            externalSpec: Payload::optionalString($data, 'externalSpec', 'data'),
            raw: $data,
        );
    }

    private static function fields(array $data): array
    {
        $fields = Payload::listOfArrays($data, 'fields', 'data');

        return array_map(
            static fn (array $field, int $index): FieldDescription => FieldDescription::fromArray(
                $field,
                "data.fields.{$index}",
            ),
            $fields,
            array_keys($fields),
        );
    }

    private static function functions(array $data): array
    {
        $functions = Payload::listOfArrays($data, 'functions', 'data');

        return array_map(
            static fn (array $function, int $index): FunctionDescription => FunctionDescription::fromArray(
                $function,
                "data.functions.{$index}",
            ),
            $functions,
            array_keys($functions),
        );
    }

    private static function indexes(array $data): array
    {
        $indexes = Payload::listOfArrays($data, 'indexes', 'data');

        return array_map(
            static fn (array $index, int $offset): IndexDescription => IndexDescription::fromArray(
                $index,
                "data.indexes.{$offset}",
            ),
            $indexes,
            array_keys($indexes),
        );
    }

    private static function properties(array $data): array
    {
        $properties = Payload::listOfArrays($data, 'properties', 'data');

        return array_map(
            static fn (array $property, int $index): CollectionProperty => CollectionProperty::fromArray(
                $property,
                "data.properties.{$index}",
            ),
            $properties,
            array_keys($properties),
        );
    }
}
