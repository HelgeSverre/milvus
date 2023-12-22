<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Query
 *
 * Conducts a vector query in a collection.
 */
class QueryVector extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    protected string $collectionName;
    protected string $filter;
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?array $outputFields = null;
    protected ?string $dbName = null;

    public function resolveEndpoint(): string
    {
        return '/v1/vector/query';
    }

    public function __construct(
        protected string $publicEndpoint, // The endpoint of your cluster.
        string $collectionName,
        string $filter,
        ?int $limit = null,
        ?int $offset = null,
        ?array $outputFields = null,
        ?string $dbName = null
    ) {
        $this->collectionName = $collectionName;
        $this->filter = $filter;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->outputFields = $outputFields;
        $this->dbName = $dbName;
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'filter' => $this->filter,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'outputFields' => $this->outputFields,
            'dbName' => $this->dbName,
        ]);
    }
}
