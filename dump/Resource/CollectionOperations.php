<?php

namespace HelgeSverre\Milvus\Resource;

use HelgeSverre\Milvus\Requests\CollectionOperations\CreateCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DescribeCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DropCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\ListCollections;
use HelgeSverre\Milvus\Resource;
use Saloon\Http\Response;

class CollectionOperations extends Resource
{
    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     * @param  string  $dbName The name of the database
     */
    public function list(string $clusterEndpoint, string $dbName): Response
    {
        return $this->connector->send(new ListCollections($clusterEndpoint, $dbName));
    }

    /**
     * @param  string  $clusterEndpoint The endpoint of your cluster.
     */
    public function create(string $clusterEndpoint): Response
    {
        return $this->connector->send(new CreateCollection($clusterEndpoint));
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
