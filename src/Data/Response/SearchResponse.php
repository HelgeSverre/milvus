<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\Entity;
use HelgeSverre\Milvus\Data\Support\Payload;
use Saloon\Http\Response;

final readonly class SearchResponse extends MilvusResponse
{
    private function __construct(
        ResponseMetadata $metadata,
        public array $entities,
        public array $recalls,
        public array $topks,
    ) {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? Entity::listFromArray(self::data($metadata)) : [],
            $metadata->successful() ? Payload::listOfNumbers($metadata->raw, 'recalls', '') : [],
            $metadata->successful() ? Payload::listOfIntegers($metadata->raw, 'topks', '') : [],
        );
    }
}
