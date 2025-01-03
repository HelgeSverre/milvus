<?php

namespace HelgeSverre\Milvus\Resource;

use HelgeSverre\Milvus\Requests\CollectionOperations\CreateCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DescribeCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DropCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\ListCollections;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class CollectionOperations extends BaseResource
{
    public function list(?string $dbName = null): Response
    {
        return $this->connector->send(new ListCollections($dbName));
    }

    public function create(
        string $collectionName,
        int $dimension,
        ?string $dbName = null,
        ?string $metricType = null,
        ?string $idType = null,
        ?string $autoID = null,
        ?string $primaryFieldName = null,
        ?string $vectorFieldName = null,
    ): Response {
        return $this->connector->send(new CreateCollection(
            collectionName: $collectionName,
            dimension: $dimension,
            dbName: $dbName,
            metricType: $metricType,
            idType: $idType,
            autoID: $autoID,
            primaryFieldName: $primaryFieldName,
            vectorFieldName: $vectorFieldName,
        ));
    }

    public function describe(string $collectionName, ?string $dbName = null): Response
    {
        return $this->connector->send(new DescribeCollection(
            collectionName: $collectionName,
            dbName: $dbName
        ));
    }

    public function drop(string $collectionName, ?string $dbName = null): Response
    {
        return $this->connector->send(new DropCollection(
            collectionName: $collectionName,
            dbName: $dbName
        ));
    }
}
