<?php

namespace HelgeSverre\Milvus\Requests\DatabaseOperations;

use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Creates a database in the current Milvus instance.
 */
class CreateDatabase extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $dbName,
        protected ?array $properties = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/databases/create';
    }

    protected function responseDto(): string
    {
        return EmptyResponse::class;
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'dbName' => $this->dbName,
            'properties' => $this->properties,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
