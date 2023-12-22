<?php

namespace HelgeSverre\Milvus\Resource;

use HelgeSverre\Milvus\Requests\VectorOperations\DeleteVector;
use HelgeSverre\Milvus\Requests\VectorOperations\GetVector;
use HelgeSverre\Milvus\Requests\VectorOperations\InsertVector;
use HelgeSverre\Milvus\Requests\VectorOperations\QueryVector;
use HelgeSverre\Milvus\Requests\VectorOperations\SearchVector;
use HelgeSverre\Milvus\Requests\VectorOperations\UpsertVector;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class VectorOperations extends BaseResource
{
    public function delete(
        int|string|array $id,
        string $collectionName,
        ?string $dbName = null,
    ): Response {
        return $this->connector->send(new DeleteVector(
            id: $id,
            collectionName: $collectionName,
            dbName: $dbName,
        ));
    }

    public function insert(
        string $collectionName,
        array $data,
        ?string $dbName = null,
    ): Response {
        return $this->connector->send(new InsertVector(
            collectionName: $collectionName,
            data: $data,
            dbName: $dbName,
        ));
    }

    public function search(string $publicEndpoint): Response
    {
        return $this->connector->send(new SearchVector($publicEndpoint));
    }

    public function query(string $publicEndpoint): Response
    {
        return $this->connector->send(new QueryVector($publicEndpoint));
    }

    public function get(
        int|string|array $id,
        string $collectionName,
        ?array $outputFields = null,
        ?string $dbName = null,
    ): Response {
        return $this->connector->send(new GetVector(
            id: $id,
            collectionName: $collectionName,
            outputFields: $outputFields,
            dbName: $dbName,
        ));
    }

    public function upsert(
        string $collectionName,
        array $data,
        ?string $dbName = null,
    ): Response {
        return $this->connector->send(new UpsertVector(
            collectionName: $collectionName,
            data: $data,
            dbName: $dbName,
        ));
    }
}
