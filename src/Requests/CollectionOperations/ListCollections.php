<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * List Collections
 *
 * Lists collections in a cluster.
 */
class ListCollections extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v2/vectordb/collections/list';
    }

    public function __construct(
        protected ?string $dbName = null,
    ) {
        $this->body()->setJsonFlags(JSON_FORCE_OBJECT);
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'dbName' => $this->dbName,
        ]);
    }
}
