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
        return '/v2/vectordb/collections/create';
    }

    public function __construct(
        protected string $collectionName,
        protected int $dimension,
        protected ?string $dbName = null,
        protected ?string $metricType = null,
        protected ?string $idType = null,
        protected ?string $autoID = null,
        protected ?string $primaryFieldName = null,
        protected ?string $vectorFieldName = null,
    ) {
    }

    public function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'dimension' => $this->dimension,
            'dbName' => $this->dbName,
            'metricType' => $this->metricType,
            'idType' => $this->idType,
            'autoID' => $this->autoID,
            'primaryFieldName' => $this->primaryFieldName,
            'vectorFieldName' => $this->vectorFieldName,
        ]);
    }
}
