<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Drop Collection
 *
 * Drops a collection. This operation erases your collection data. Exercise caution when performing
 * this operation.
 */
class DropCollection extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections/drop';
    }

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     */
    public function __construct(
        protected string $clusterEndpoint,
    ) {
    }
}
