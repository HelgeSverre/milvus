<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\CollectionDescription;
use Saloon\Http\Response;

final readonly class CollectionDescriptionResponse extends MilvusResponse
{
    private function __construct(
        ResponseMetadata $metadata,
        public ?CollectionDescription $collection,
    ) {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? CollectionDescription::fromArray(self::data($metadata)) : null,
        );
    }
}
