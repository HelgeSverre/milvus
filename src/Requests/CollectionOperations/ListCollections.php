<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * List Collections
 *
 * Lists collections in a cluster.
 */
class ListCollections extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections';
    }

    public function __construct(
        protected ?string $dbName = null,
    ) {
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'dbName' => $this->dbName,
        ]);
    }
}
