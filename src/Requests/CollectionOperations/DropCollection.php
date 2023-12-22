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

    public function __construct(
        protected string $collectionName,
        protected ?string $dbName = null
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
