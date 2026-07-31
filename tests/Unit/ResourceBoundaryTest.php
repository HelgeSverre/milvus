<?php

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\CollectionListResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseListResponse;
use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Data\Response\EntityResponse;
use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Data\Response\SearchResponse;
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Requests\CollectionOperations\CreateCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DescribeCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DropCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\ListCollections;
use HelgeSverre\Milvus\Requests\DatabaseOperations\CreateDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\DescribeDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\DropDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\ListDatabases;
use HelgeSverre\Milvus\Requests\VectorOperations\DeleteVector;
use HelgeSverre\Milvus\Requests\VectorOperations\GetVector;
use HelgeSverre\Milvus\Requests\VectorOperations\InsertVector;
use HelgeSverre\Milvus\Requests\VectorOperations\QueryVector;
use HelgeSverre\Milvus\Requests\VectorOperations\SearchVector;
use HelgeSverre\Milvus\Requests\VectorOperations\UpsertVector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('preserves the public resource contract through request construction and response decoding', function (
    Closure $operation,
    string $requestClass,
    array $expectedBody,
    array $responseBody,
    string $dtoClass,
    Closure $dtoValue,
    mixed $expectedDtoValue,
) {
    $mockClient = new MockClient([
        $requestClass => MockResponse::make($responseBody, 200),
    ]);
    $milvus = new Milvus(null, 'localhost', '19530');
    $milvus->withMockClient($mockClient);

    $response = $operation($milvus);
    $dto = $response->dto()->throwIfFailed();

    expect($response->status())->toBe(200)
        ->and($dto)->toBeInstanceOf($dtoClass)
        ->and($dtoValue($dto))->toBe($expectedDtoValue);

    $mockClient->assertSent(
        fn (Request $request): bool => $request::class === $requestClass
            && $request->body()->all() === $expectedBody,
    );
    $mockClient->assertSentCount(1);
})->with([
    'list databases' => [
        fn (Milvus $milvus) => $milvus->databases()->list(),
        ListDatabases::class,
        [],
        ['code' => 0, 'data' => ['default', 'analytics']],
        DatabaseListResponse::class,
        fn (DatabaseListResponse $dto) => $dto->databases,
        ['default', 'analytics'],
    ],
    'create database' => [
        fn (Milvus $milvus) => $milvus->databases()->create(
            dbName: 'analytics',
            properties: [
                'database.replica.number' => 3,
                'database.force.deny.writing' => false,
            ],
        ),
        CreateDatabase::class,
        [
            'dbName' => 'analytics',
            'properties' => [
                'database.replica.number' => 3,
                'database.force.deny.writing' => false,
            ],
        ],
        ['code' => 0, 'data' => []],
        EmptyResponse::class,
        fn (EmptyResponse $dto) => $dto->data,
        [],
    ],
    'describe database' => [
        fn (Milvus $milvus) => $milvus->databases()->describe('analytics'),
        DescribeDatabase::class,
        ['dbName' => 'analytics'],
        [
            'code' => 0,
            'data' => [
                'dbName' => 'analytics',
                'dbID' => '18446744073709551615',
                'properties' => [[
                    'key' => 'timezone',
                    'value' => 'UTC',
                    'futurePropertyField' => true,
                ]],
                'futureDatabaseField' => 'retained',
            ],
        ],
        DatabaseDescriptionResponse::class,
        fn (DatabaseDescriptionResponse $dto) => [
            $dto->database?->dbName,
            $dto->database?->dbId,
            $dto->database?->properties[0]->value,
            $dto->database?->raw['futureDatabaseField'],
        ],
        ['analytics', '18446744073709551615', 'UTC', 'retained'],
    ],
    'drop database' => [
        fn (Milvus $milvus) => $milvus->databases()->drop('analytics'),
        DropDatabase::class,
        ['dbName' => 'analytics'],
        ['code' => 0, 'data' => []],
        EmptyResponse::class,
        fn (EmptyResponse $dto) => $dto->data,
        [],
    ],
    'list collections' => [
        fn (Milvus $milvus) => $milvus->collections()->list('analytics'),
        ListCollections::class,
        ['dbName' => 'analytics'],
        ['code' => 0, 'data' => ['documents', 'images']],
        CollectionListResponse::class,
        fn (CollectionListResponse $dto) => $dto->collections,
        ['documents', 'images'],
    ],
    'create collection with quick setup' => [
        fn (Milvus $milvus) => $milvus->collections()->create(
            collectionName: 'documents',
            dimension: 128,
            dbName: 'analytics',
            metricType: 'COSINE',
            idType: 'Int64',
            autoID: false,
            primaryFieldName: 'id',
            vectorFieldName: 'embedding',
            params: ['enableDynamicField' => true],
            description: '',
        ),
        CreateCollection::class,
        [
            'collectionName' => 'documents',
            'dimension' => 128,
            'dbName' => 'analytics',
            'metricType' => 'COSINE',
            'idType' => 'Int64',
            'autoID' => false,
            'primaryFieldName' => 'id',
            'vectorFieldName' => 'embedding',
            'params' => ['enableDynamicField' => true],
            'description' => '',
        ],
        ['code' => 0, 'data' => ['acknowledged' => true]],
        EmptyResponse::class,
        fn (EmptyResponse $dto) => $dto->data['acknowledged'] ?? null,
        true,
    ],
    'create collection with custom schema' => [
        fn (Milvus $milvus) => $milvus->collections()->create(
            collectionName: 'documents',
            dbName: 'analytics',
            schema: ['autoID' => false, 'fields' => []],
            indexParams: [['fieldName' => 'embedding', 'metricType' => 'COSINE']],
            params: ['consistencyLevel' => 'Strong'],
            description: 'Searchable documents',
        ),
        CreateCollection::class,
        [
            'collectionName' => 'documents',
            'dbName' => 'analytics',
            'schema' => ['autoID' => false, 'fields' => []],
            'indexParams' => [['fieldName' => 'embedding', 'metricType' => 'COSINE']],
            'params' => ['consistencyLevel' => 'Strong'],
            'description' => 'Searchable documents',
        ],
        ['code' => 0, 'data' => ['acknowledged' => true]],
        EmptyResponse::class,
        fn (EmptyResponse $dto) => $dto->data['acknowledged'] ?? null,
        true,
    ],
    'describe collection' => [
        fn (Milvus $milvus) => $milvus->collections()->describe('documents', 'analytics'),
        DescribeCollection::class,
        ['collectionName' => 'documents', 'dbName' => 'analytics'],
        ['code' => 0, 'data' => ['collectionName' => 'documents']],
        CollectionDescriptionResponse::class,
        fn (CollectionDescriptionResponse $dto) => $dto->collection?->collectionName,
        'documents',
    ],
    'drop collection' => [
        fn (Milvus $milvus) => $milvus->collections()->drop('documents', 'analytics'),
        DropCollection::class,
        ['collectionName' => 'documents', 'dbName' => 'analytics'],
        ['code' => 0, 'data' => ['acknowledged' => true]],
        EmptyResponse::class,
        fn (EmptyResponse $dto) => $dto->data['acknowledged'] ?? null,
        true,
    ],
    'insert entities' => [
        fn (Milvus $milvus) => $milvus->vector()->insert(
            collectionName: 'documents',
            data: [['id' => 1, 'title' => 'First']],
            dbName: 'analytics',
            partitionName: 'published',
            partialUpdate: false,
        ),
        InsertVector::class,
        [
            'data' => [['id' => 1, 'title' => 'First']],
            'collectionName' => 'documents',
            'dbName' => 'analytics',
            'partitionName' => 'published',
            'partialUpdate' => false,
        ],
        ['code' => 0, 'data' => ['insertCount' => 1, 'insertIds' => ['1']]],
        MutationResponse::class,
        fn (MutationResponse $dto) => [$dto->result?->affectedCount(), $dto->result?->insertIds],
        [1, ['1']],
    ],
    'upsert entities' => [
        fn (Milvus $milvus) => $milvus->vector()->upsert(
            collectionName: 'documents',
            data: [['id' => 2, 'title' => 'Second']],
            dbName: 'analytics',
            partitionName: 'published',
            partialUpdate: true,
        ),
        UpsertVector::class,
        [
            'data' => [['id' => 2, 'title' => 'Second']],
            'collectionName' => 'documents',
            'dbName' => 'analytics',
            'partitionName' => 'published',
            'partialUpdate' => true,
        ],
        ['code' => 0, 'data' => ['upsertCount' => 1, 'upsertIds' => [2]]],
        MutationResponse::class,
        fn (MutationResponse $dto) => [$dto->result?->affectedCount(), $dto->result?->upsertIds],
        [1, [2]],
    ],
    'delete entities' => [
        fn (Milvus $milvus) => $milvus->vector()->delete(
            collectionName: 'documents',
            dbName: 'analytics',
            partitionName: 'published',
            id: 0,
            exprParams: ['tenant' => 'acme'],
        ),
        DeleteVector::class,
        [
            'collectionName' => 'documents',
            'filter' => 'id == 0',
            'dbName' => 'analytics',
            'partitionName' => 'published',
            'exprParams' => ['tenant' => 'acme'],
        ],
        ['code' => 0, 'data' => ['deleteCount' => 1, 'deleteIds' => [0]]],
        MutationResponse::class,
        fn (MutationResponse $dto) => [$dto->result?->affectedCount(), $dto->result?->deleteIds],
        [1, [0]],
    ],
    'get entities' => [
        fn (Milvus $milvus) => $milvus->vector()->get(
            id: [1, '2'],
            collectionName: 'documents',
            outputFields: ['title'],
            dbName: 'analytics',
            partitionNames: ['published'],
            consistencyLevel: 'Strong',
            partitionName: 'published',
        ),
        GetVector::class,
        [
            'id' => [1, '2'],
            'collectionName' => 'documents',
            'outputFields' => ['title'],
            'dbName' => 'analytics',
            'partitionNames' => ['published'],
            'consistencyLevel' => 'Strong',
            'partitionName' => 'published',
        ],
        ['code' => 0, 'data' => [['id' => '2', 'title' => 'Second']]],
        EntityResponse::class,
        fn (EntityResponse $dto) => [$dto->entities[0]->id, $dto->entities[0]->field('title')],
        ['2', 'Second'],
    ],
    'query entities' => [
        fn (Milvus $milvus) => $milvus->vector()->query(
            collectionName: 'documents',
            filter: '',
            limit: 0,
            offset: 0,
            outputFields: ['title'],
            dbName: 'analytics',
            partitionNames: ['published'],
            exprParams: ['tenant' => 'acme'],
            consistencyLevel: 'Strong',
        ),
        QueryVector::class,
        [
            'collectionName' => 'documents',
            'filter' => '',
            'limit' => 0,
            'offset' => 0,
            'outputFields' => ['title'],
            'dbName' => 'analytics',
            'partitionNames' => ['published'],
            'exprParams' => ['tenant' => 'acme'],
            'consistencyLevel' => 'Strong',
        ],
        ['code' => 0, 'data' => [['id' => 1, 'title' => 'First']]],
        EntityResponse::class,
        fn (EntityResponse $dto) => [$dto->entities[0]->id, $dto->entities[0]->field('title')],
        [1, 'First'],
    ],
    'search entities' => [
        fn (Milvus $milvus) => $milvus->vector()->search(
            collectionName: 'documents',
            data: [[0.1, 0.2]],
            annsField: 'embedding',
            filter: '',
            limit: 0,
            offset: 0,
            groupingField: 'category',
            groupSize: 1,
            strictGroupSize: false,
            outputFields: ['title'],
            searchParams: ['metricType' => 'COSINE'],
            dbName: 'analytics',
            partitionNames: ['published'],
            consistencyLevel: 'Strong',
            exprParams: ['tenant' => 'acme'],
            functionScore: ['name' => 'rerank'],
            params: ['ef' => 64],
        ),
        SearchVector::class,
        [
            'collectionName' => 'documents',
            'data' => [[0.1, 0.2]],
            'annsField' => 'embedding',
            'filter' => '',
            'limit' => 0,
            'offset' => 0,
            'groupingField' => 'category',
            'groupSize' => 1,
            'strictGroupSize' => false,
            'outputFields' => ['title'],
            'searchParams' => ['metricType' => 'COSINE'],
            'dbName' => 'analytics',
            'partitionNames' => ['published'],
            'consistencyLevel' => 'Strong',
            'exprParams' => ['tenant' => 'acme'],
            'functionScore' => ['name' => 'rerank'],
            'params' => ['ef' => 64],
        ],
        [
            'code' => 0,
            'data' => [['id' => 1, 'distance' => 0.125, 'title' => 'First']],
            'recalls' => [0.95],
            'topks' => [1],
        ],
        SearchResponse::class,
        fn (SearchResponse $dto) => [
            $dto->entities[0]->id,
            $dto->entities[0]->distance,
            $dto->entities[0]->field('title'),
            $dto->recalls,
            $dto->topks,
        ],
        [1, 0.125, 'First', [0.95], [1]],
    ],
    'search entities by ID' => [
        fn (Milvus $milvus) => $milvus->vector()->search(
            collectionName: 'documents',
            data: null,
            annsField: 'embedding',
            outputFields: ['title'],
            ids: [1, '2'],
        ),
        SearchVector::class,
        [
            'collectionName' => 'documents',
            'annsField' => 'embedding',
            'outputFields' => ['title'],
            'ids' => [1, '2'],
        ],
        [
            'code' => 0,
            'data' => [['id' => '2', 'distance' => 0.25, 'title' => 'Second']],
            'recalls' => [1],
            'topks' => [1],
        ],
        SearchResponse::class,
        fn (SearchResponse $dto) => [
            $dto->entities[0]->id,
            $dto->entities[0]->distance,
            $dto->entities[0]->field('title'),
        ],
        ['2', 0.25, 'Second'],
    ],
]);
