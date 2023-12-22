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

    protected string $collectionName;
    protected array $vector;
    protected ?string $filter = null;
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?array $outputFields = null;
    protected ?array $params = null;
    protected ?string $dbName = null;

    public function resolveEndpoint(): string
    {
        return '/v1/vector/search';
    }

    public function __construct(
        protected string $publicEndpoint, // The endpoint of your cluster.
        string $collectionName,
        array $vector,
        ?string $filter = null,
        ?int $limit = null,
        ?int $offset = null,
        ?array $outputFields = null,
        ?array $params = null,
        ?string $dbName = null
    ) {
        $this->collectionName = $collectionName;
        $this->vector = $vector;
        $this->filter = $filter;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->outputFields = $outputFields;
        $this->params = $params;
        $this->dbName = $dbName;
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'vector' => $this->vector,
            'filter' => $this->filter,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'outputFields' => $this->outputFields,
            'params' => $this->params,
            'dbName' => $this->dbName,
        ]);
    }
}
