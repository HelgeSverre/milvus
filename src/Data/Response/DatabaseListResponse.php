<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\Support\Payload;
use HelgeSverre\Milvus\Exceptions\InvalidResponseException;
use Saloon\Http\Response;

final readonly class DatabaseListResponse extends MilvusResponse
{
    private function __construct(ResponseMetadata $metadata, public array $databases)
    {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? self::databases(self::data($metadata)) : [],
        );
    }

    private static function databases(array $data): array
    {
        Payload::assertList($data, 'data');

        foreach ($data as $index => $database) {
            if (! is_string($database)) {
                throw InvalidResponseException::expected("data.{$index}", 'string', $database);
            }
        }

        return $data;
    }
}
