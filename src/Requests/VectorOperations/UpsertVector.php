<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Upsert
 *
 * Inserts one or more entities into a collection.
 */
class UpsertVector extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/upsert';
    }

    protected function responseDto(): string
    {
        return MutationResponse::class;
    }

    public function __construct(
        protected string $collectionName,
        protected array $data,
        protected ?string $dbName = null,
        protected ?string $partitionName = null,
        protected ?bool $partialUpdate = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'data' => $this->data,
            'collectionName' => $this->collectionName,
            'dbName' => $this->dbName,
            'partitionName' => $this->partitionName,
            'partialUpdate' => $this->partialUpdate,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
