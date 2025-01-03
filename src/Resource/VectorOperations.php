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
        string $collectionName,
        string $filter,
        ?string $dbName = null,
        ?string $partitionName = null,
    ): Response {
        return $this->connector->send(new DeleteVector(
            collectionName: $collectionName,
            filter: $filter,
            dbName: $dbName,
            partitionName: $partitionName,
        ));
    }

    public function insert(
        string $collectionName,
        array $data,
        ?string $dbName = null,
        ?string $partitionName = null,
    ): Response {
        return $this->connector->send(new InsertVector(
            collectionName: $collectionName,
            data: $data,
            dbName: $dbName,
            partitionName: $partitionName,
        ));
    }

    public function search(
        string $collectionName,
        array $data,
        string $annsField,
        ?string $filter = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $groupingField = null,
        ?int $groupSize = null,
        ?bool $strictGroupSize = null,
        ?array $outputFields = null,
        ?array $searchParams = null,
        ?string $dbName = null,
        ?array $partitionNames = null,
    ): Response {
        return $this->connector->send(new SearchVector(
            collectionName: $collectionName,
            data: $data,
            annsField: $annsField,
            filter: $filter,
            limit: $limit,
            offset: $offset,
            groupingField: $groupingField,
            groupSize: $groupSize,
            strictGroupSize: $strictGroupSize,
            outputFields: $outputFields,
            searchParams: $searchParams,
            dbName: $dbName,
            partitionNames: $partitionNames,
        ));
    }

    public function query(
        string $collectionName,
        string $filter,
        ?int $limit = null,
        ?int $offset = null,
        ?array $outputFields = null,
        ?string $dbName = null,
        ?array $partitionNames = null,
    ): Response {
        return $this->connector->send(new QueryVector(
            collectionName: $collectionName,
            filter: $filter,
            limit: $limit,
            offset: $offset,
            outputFields: $outputFields,
            dbName: $dbName,
            partitionNames: $partitionNames,
        ));
    }

    public function get(
        int|string|array $id,
        string $collectionName,
        ?array $outputFields = null,
        ?string $dbName = null,
        ?array $partitionNames = null,
    ): Response {
        return $this->connector->send(new GetVector(
            id: $id,
            collectionName: $collectionName,
            outputFields: $outputFields,
            dbName: $dbName,
            partitionNames: $partitionNames,
        ));
    }

    public function upsert(
        string $collectionName,
        array $data,
        ?string $dbName = null,
        ?string $partitionName = null,
    ): Response {
        return $this->connector->send(new UpsertVector(
            collectionName: $collectionName,
            data: $data,
            dbName: $dbName,
            partitionName: $partitionName,
        ));
    }
}
