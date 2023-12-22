<?php

namespace HelgeSverre\Milvus\Requests\CollectionOperations;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Collection
 *
 * Creates a collection in a cluster.
 */
class CreateCollection extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections/create';
    }

    // Constructor with promoted properties has been removed or modified.
    // Please provide the current constructor for accurate diff generation.

    public function defaultQuery(): array
    {
        // The content of the defaultQuery method has been modified or removed.
        // Please provide the current content of the defaultQuery method for accurate diff generation.
    }
}

++ src/Requests/CollectionOperations/DropCollection.php
++ src/Requests/CollectionOperations/DropCollection.php

    public function resolveEndpoint(): string
    {
        return '/v1/vector/collections/create';
    }
}
