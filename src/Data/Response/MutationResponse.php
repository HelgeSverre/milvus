<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\MutationResult;
use Saloon\Http\Response;

final readonly class MutationResponse extends MilvusResponse
{
    private function __construct(
        ResponseMetadata $metadata,
        public ?MutationResult $result,
    ) {
        parent::__construct($metadata);
    }

    public static function fromResponse(Response $response): static
    {
        $metadata = self::metadata($response);

        return new self(
            $metadata,
            $metadata->successful() ? MutationResult::fromArray(self::data($metadata)) : null,
        );
    }
}
