<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Describe Collection
 *
 * Describes the details of a collection.
 */
class DescribeCollection extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections/describe';
    }

    /**
     * @param  string  $collectionName The name of the collection to describe.
     */
    public function __construct(
        protected string $collectionName
    ) {
    }

    public function defaultQuery(): array
    {
        return ['collectionName' => $this->collectionName, 'dbName' => $this->dbName];
    }
}
