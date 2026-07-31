<?php

namespace HelgeSverre\Milvus\Requests\DatabaseOperations;

use HelgeSverre\Milvus\Data\Response\DatabaseDescriptionResponse;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Describes a database in the current Milvus instance.
 */
class DescribeDatabase extends MilvusRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(protected string $dbName) {}

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/databases/describe';
    }

    protected function responseDto(): string
    {
        return DatabaseDescriptionResponse::class;
    }

    protected function defaultBody(): array
    {
        return ['dbName' => $this->dbName];
    }
}
