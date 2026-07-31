<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use HelgeSverre\Milvus\Data\Response\EntityResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Query
 *
 * Conducts a vector query in a collection.
 */
class QueryVector extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/query';
    }

    protected function responseDto(): string
    {
        return EntityResponse::class;
    }

    public function __construct(
        protected string $collectionName,
        protected ?string $filter = null,
        protected ?int $limit = null,
        protected ?int $offset = null,
        protected ?array $outputFields = null,
        protected ?string $dbName = null,
        protected ?array $partitionNames = null,
        protected ?array $exprParams = null,
        protected ?string $consistencyLevel = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'filter' => $this->filter,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'outputFields' => $this->outputFields,
            'dbName' => $this->dbName,
            'partitionNames' => $this->partitionNames,
            'exprParams' => $this->exprParams,
            'consistencyLevel' => $this->consistencyLevel,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
