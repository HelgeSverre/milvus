<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Delete
 *
 * Deletes one or more entities from a collection.
 */
class DeleteVector extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/delete';
    }

    public function __construct(
        protected string $collectionName,
        protected ?string $filter = null,
        protected ?string $dbName = null,
        protected ?string $partitionName = null,
        protected int|string|null $id = null,
        protected ?array $exprParams = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'filter' => $this->filter,
            'dbName' => $this->dbName,
            'partitionName' => $this->partitionName,
            'id' => $this->id,
            'exprParams' => $this->exprParams,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
