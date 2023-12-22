<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Collection
 *
 * Creates a collection in a cluster.
 */
class CreateCollection extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $clusterEndpoint,
        protected ?string $dbName = null,
    ) {
    }

    public function defaultQuery(): array
    {
        return array_filter([
            'dbName' => $this->dbName,
        ]);
    }

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections/create';
    }
}

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     */
++ dump/Requests/CollectionOperations/DescribeCollection.php
