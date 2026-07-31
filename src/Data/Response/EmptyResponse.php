<?php

namespace HelgeSverre\Milvus\Data\Response;

use Saloon\Http\Response;

final readonly class EmptyResponse extends MilvusResponse
{
    private function __construct(ResponseMetadata $metadata, public array $data)
    {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self($metadata, $metadata->successful() ? self::data($metadata) : []);
    }
}
