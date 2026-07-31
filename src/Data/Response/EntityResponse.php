<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\Entity;
use Saloon\Http\Response;

final readonly class EntityResponse extends MilvusResponse
{
    private function __construct(ResponseMetadata $metadata, public array $entities)
    {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? Entity::listFromArray(self::data($metadata)) : [],
        );
    }
}
