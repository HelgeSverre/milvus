<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Search
 *
 * Conducts a similarity search in a collection.
 */
class SearchVector extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/search';
    }

    public function __construct(
        protected string $collectionName,
        protected array $data,
        protected string $annsField,
        protected ?string $filter = null,
        protected ?int $limit = null,
        protected ?int $offset = null,
        protected ?string $groupingField = null,
        protected ?int $groupSize = null,
        protected ?bool $strictGroupSize = null,
        protected ?array $outputFields = null,
        protected ?array $searchParams = null,
        protected ?string $dbName = null,
        protected ?array $partitionNames = null,
    ) {
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'data' => $this->data,
            'annsField' => $this->annsField,
            'filter' => $this->filter,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'groupingField' => $this->groupingField,
            'groupSize' => $this->groupSize,
            'strictGroupSize' => $this->strictGroupSize,
            'outputFields' => $this->outputFields,

            // TODO: restful api only supports "radius" and "range_filter at this time.
            'searchParams' => $this->searchParams,
            'dbName' => $this->dbName,
            'partitionNames' => $this->partitionNames,
        ]);
    }
}
