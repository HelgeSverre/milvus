<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Describe Collection
 *
 * Describes the details of a collection.
 */
class DescribeCollection extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/collections/describe';
    }

    /**
     * @param  string  $collectionName The name of the collection to describe.
     */
    public function __construct(
        protected string $collectionName,
        protected ?string $dbName = null,
    ) {
    }

    public function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'dbName' => $this->dbName,
        ]);
    }
}
