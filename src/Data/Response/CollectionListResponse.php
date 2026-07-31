<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\Support\Payload;
use HelgeSverre\Milvus\Exceptions\InvalidResponseException;
use Saloon\Http\Response;

final readonly class CollectionListResponse extends MilvusResponse
{
    private function __construct(ResponseMetadata $metadata, public array $collections)
    {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? self::collections(self::data($metadata)) : [],
        );
    }

    private static function collections(array $data): array
    {
        Payload::assertList($data, 'data');

        foreach ($data as $index => $collection) {
            if (! is_string($collection)) {
                throw InvalidResponseException::expected("data.{$index}", 'string', $collection);
            }
        }

        return $data;
    }
}
