<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Upsert
 *
 * Inserts one or more entities into a collection.
 */
class UpsertVector extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/upsert';
    }

    public function __construct(
        protected string $collectionName,
        protected array $data,
        protected ?string $dbName = null,
        protected ?string $partitionName = null,
    ) {
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'data' => $this->data,
            'collectionName' => $this->collectionName,
            'dbName' => $this->dbName,
            'partitionName' => $this->partitionName,
        ]);
    }
}
