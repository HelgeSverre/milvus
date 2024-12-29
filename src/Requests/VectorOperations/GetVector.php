<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Get
 *
 * Gets entities by the specified IDs.
 */
class GetVector extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/get';
    }

    public function __construct(
        protected int|string|array $id,
        protected string $collectionName,
        protected ?array $outputFields = null,
        protected ?string $dbName = null,
        protected ?array $partitionNames = null,
    ) {
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'id' => $this->id,
            'collectionName' => $this->collectionName,
            'outputFields' => $this->outputFields,
            'dbName' => $this->dbName,
            'partitionNames' => $this->partitionNames,
        ]);
    }
}
