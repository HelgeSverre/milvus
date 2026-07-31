<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Drop Collection
 *
 * Drops a collection. This operation erases your collection data. Exercise caution when performing
 * this operation.
 */
class DropCollection extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/collections/drop';
    }

    protected function responseDto(): string
    {
        return EmptyResponse::class;
    }

    public function __construct(
        protected string $collectionName,
        protected ?string $dbName = null,
    ) {}

    public function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'dbName' => $this->dbName,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
