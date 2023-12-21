<?php

namespace HelgeSverre\Milvus\Resource;

use HelgeSverre\Milvus\Requests\VectorOperations\DeleteVector;
use HelgeSverre\Milvus\Requests\VectorOperations\GetVector;
use HelgeSverre\Milvus\Requests\VectorOperations\InsertVector;
use HelgeSverre\Milvus\Requests\VectorOperations\QueryVector;
use HelgeSverre\Milvus\Requests\VectorOperations\SearchVector;
use HelgeSverre\Milvus\Requests\VectorOperations\UpsertVector;
use HelgeSverre\Milvus\Resource;
use Saloon\Http\Response;

class VectorOperations extends Resource
{
    public function delete(string $publicEndpoint): Response
    {
        return $this->connector->send(new DeleteVector($publicEndpoint));
    }

    public function insert(string $publicEndpoint): Response
    {
        return $this->connector->send(new InsertVector($publicEndpoint));
    }

    public function search(string $publicEndpoint): Response
    {
        return $this->connector->send(new SearchVector($publicEndpoint));
    }

    public function query(string $publicEndpoint): Response
    {
        return $this->connector->send(new QueryVector($publicEndpoint));
    }

    public function get(string $publicEndpoint): Response
    {
        return $this->connector->send(new GetVector($publicEndpoint));
    }

    public function upsert(string $publicEndpoint): Response
    {
        return $this->connector->send(new UpsertVector($publicEndpoint));
    }
}
