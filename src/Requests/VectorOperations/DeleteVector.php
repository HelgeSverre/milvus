<?php

namespace HelgeSverre\Milvus\Requests\VectorOperations;

use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Delete
 *
 * Deletes one or more entities from a collection.
 */
class DeleteVector extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/entities/delete';
    }

    protected function responseDto(): string
    {
        return MutationResponse::class;
    }

    public function __construct(
        protected string $collectionName,
        protected ?string $filter = null,
        protected ?string $dbName = null,
        protected ?string $partitionName = null,
        protected int|string|null $id = null,
        protected ?array $exprParams = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'collectionName' => $this->collectionName,
            'filter' => $this->resolvedFilter(),
            'dbName' => $this->dbName,
            'partitionName' => $this->partitionName,
            'exprParams' => $this->exprParams,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function resolvedFilter(): ?string
    {
        if ($this->filter !== null) {
            return $this->filter;
        }

        if ($this->id === null) {
            return null;
        }

        $id = is_int($this->id)
            ? (string) $this->id
            : json_encode($this->id, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return "id == {$id}";
    }
}
