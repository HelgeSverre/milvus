<?php

namespace HelgeSverre\Milvus\Resource;

use HelgeSverre\Milvus\Requests\DatabaseOperations\CreateDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\DescribeDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\DropDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\ListDatabases;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class DatabaseOperations extends BaseResource
{
    public function list(): Response
    {
        return $this->connector->send(new ListDatabases);
    }

    public function create(string $dbName, ?array $properties = null): Response
    {
        return $this->connector->send(new CreateDatabase(
            dbName: $dbName,
            properties: $properties,
        ));
    }

    public function describe(string $dbName): Response
    {
        return $this->connector->send(new DescribeDatabase($dbName));
    }

    public function drop(string $dbName): Response
    {
        return $this->connector->send(new DropDatabase($dbName));
    }
}
