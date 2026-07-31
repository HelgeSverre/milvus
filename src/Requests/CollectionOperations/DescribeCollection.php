<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Describe Collection
 *
 * Describes the details of a collection.
 */
class DescribeCollection extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/collections/describe';
    }

    protected function responseDto(): string
    {
        return CollectionDescriptionResponse::class;
    }

    /**
     * @param  string  $collectionName  The name of the collection to describe.
     */
    public function __construct(
        protected string $collectionName,
        protected ?string $dbName = null,
    ) {}

    public function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'dbName' => $this->dbName,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
