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

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     * @param  string  $dbName The name of the database
     */
    public function __construct(
        protected string $clusterEndpoint,
        protected string $dbName,
    ) {
    }

    public function defaultQuery(): array
    {
        return ['dbName' => $this->dbName];
    }
}
