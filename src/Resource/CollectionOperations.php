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
    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     * @param  string  $dbName The name of the database.
     * @param  string  $collectionName The name of the collection to create.
     * @param  int  $dimension The number of dimensions for the vector field of the collection.
     * @param  string  $metricType The distance metric used for the collection.
     * @param  string  $primaryField The primary key field.
     * @param  string  $vectorField The vector field.
     * @param  string|null  $description The description of the collection.
     */
    public function list(string $clusterEndpoint, string $dbName): Response
    {
        return $this->connector->send(new ListCollections($clusterEndpoint, $dbName));
    }

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     */
    public function create(
        string $clusterEndpoint,
        string $dbName,
        string $collectionName,
        int $dimension,
        string $metricType = 'L2',
        string $primaryField = 'id',
        string $vectorField = 'vector',
        ?string $description = null
    ): Response {
        return $this->connector->send(new CreateCollection(
            $dbName,
            $collectionName,
            $dimension,
            $metricType,
            $primaryField,
            $vectorField,
            $description
        ));
    }

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     * @param  string  $collectionName The name of the collection to describe.
     * @param  string  $dbName The name of the database.
     */
    public function describe(string $clusterEndpoint, string $collectionName, string $dbName): Response
    {
        return $this->connector->send(new DescribeCollection($clusterEndpoint, $collectionName, $dbName));
    }

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     */
    public function drop(string $clusterEndpoint): Response
    {
        return $this->connector->send(new DropCollection($clusterEndpoint));
    }
}
