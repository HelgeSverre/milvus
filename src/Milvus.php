<?php

namespace HelgeSverre\Milvus;

use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use SensitiveParameter;

/**
 * Restful API
 */
class Milvus extends Connector
{
    public function __construct(
        #[SensitiveParameter]
        protected readonly ?string $token,
        protected string $host,
        protected string $port
    ) {
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
    }

    public function resolveBaseUrl(): string
    {
        return "{$this->host}:{$this->port}";
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
