<?php

namespace HelgeSverre\Milvus\Requests\DatabaseOperations;

use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Drops a database from the current Milvus instance.
 */
class DropDatabase extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(protected string $dbName) {}

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/databases/drop';
    }

    protected function responseDto(): string
    {
        return EmptyResponse::class;
    }

    protected function defaultBody(): array
    {
        return ['dbName' => $this->dbName];
    }
}
