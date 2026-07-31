<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Collection
 *
 * Creates a collection in a cluster.
 */
class CreateCollection extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/collections/create';
    }

    protected function responseDto(): string
    {
        return EmptyResponse::class;
    }

    public function __construct(
        protected string $collectionName,
        protected ?int $dimension = null,
        protected ?string $dbName = null,
        protected ?string $metricType = null,
        protected ?string $idType = null,
        protected ?bool $autoID = null,
        protected ?string $primaryFieldName = null,
        protected ?string $vectorFieldName = null,
        protected ?array $schema = null,
        protected ?array $indexParams = null,
        protected ?array $params = null,
        protected ?string $description = null,
    ) {}

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
            'schema' => $this->schema,
            'indexParams' => $this->indexParams,
            'params' => $this->params,
            'description' => $this->description,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
