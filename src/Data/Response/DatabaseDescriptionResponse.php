<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\DatabaseDescription;
use Saloon\Http\Response;

final readonly class DatabaseDescriptionResponse extends MilvusResponse
{
    private function __construct(
        ResponseMetadata $metadata,
        public ?DatabaseDescription $database,
    ) {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? DatabaseDescription::fromArray(self::data($metadata)) : null,
        );
    }
}
