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
        return '/v1/vector/delete';
    }

    public function __construct(
        protected int|string|array $id,
        protected string $collectionName,
        protected ?string $dbName = null,
    ) {
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'id' => $this->id,
            'collectionName' => $this->collectionName,
            'dbName' => $this->dbName,
        ]);
    }
}
