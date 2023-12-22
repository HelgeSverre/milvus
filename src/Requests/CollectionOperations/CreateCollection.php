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

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections/create';
    }

    public function __construct(
        protected string $dbName,
        protected string $collectionName,
        protected int $dimension,
        protected string $metricType = 'L2',
        protected string $primaryField = 'id',
        protected string $vectorField = 'vector',
        protected ?string $description = null,
    ) {
    }

    public function defaultQuery(): array
    {
        return array_filter([
            'dbName' => $this->dbName,
            'collectionName' => $this->collectionName,
            'dimension' => $this->dimension,
            'metricType' => $this->metricType,
            'primaryField' => $this->primaryField,
            'vectorField' => $this->vectorField,
            'description' => $this->description,
        ]);
    }
}
