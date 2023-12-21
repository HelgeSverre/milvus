<?php

namespace HelgeSverre\Milvus;

use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;
use Saloon\Http\Connector;

/**
 * Restful API
 */
class Milvus extends Connector
{
    public function resolveBaseUrl(): string
    {
        return '/';
    }

    public function collections(): CollectionOperations
    {
        return new CollectionOperations($this);
    }

    public function vector(): VectorOperations
    {
        return new VectorOperations($this);
    }
}
