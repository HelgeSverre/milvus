<?php

namespace HelgeSverre\Milvus\Requests\DatabaseOperations;

use HelgeSverre\Milvus\Data\Response\DatabaseListResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Lists all databases in the current Milvus instance.
 */
class ListDatabases extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct()
    {
        $this->body()->setJsonFlags(JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT);
    }

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/databases/list';
    }

    protected function responseDto(): string
    {
        return DatabaseListResponse::class;
    }

    protected function defaultBody(): array
    {
        return [];
    }
}
