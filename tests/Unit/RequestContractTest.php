<?php

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
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Request;

it('matches the Milvus REST v2 request contract', function (Request $request, string $endpoint, array $body) {
    expect($request->getMethod())->toBe(Method::POST)
        ->and($request->resolveEndpoint())->toBe($endpoint)
        ->and($request->body()->all())->toBe($body);
})->with([
    'create database' => [
        new CreateDatabase(
            dbName: 'analytics',
            properties: [
                'database.replica.number' => 3,
                'database.force.deny.writing' => false,
                'timezone' => '',
            ],
        ),
        '/v2/vectordb/databases/create',
        [
            'dbName' => 'analytics',
            'properties' => [
                'database.replica.number' => 3,
                'database.force.deny.writing' => false,
                'timezone' => '',
            ],
        ],
    ],
    'list databases' => [
        new ListDatabases,
        '/v2/vectordb/databases/list',
        [],
    ],
    'describe database' => [
        new DescribeDatabase('analytics'),
        '/v2/vectordb/databases/describe',
        ['dbName' => 'analytics'],
    ],
    'drop database' => [
        new DropDatabase('analytics'),
        '/v2/vectordb/databases/drop',
        ['dbName' => 'analytics'],
    ],
    'create collection (quick setup)' => [
        new CreateCollection(
            collectionName: 'documents',
            dimension: 128,
            dbName: 'default',
            metricType: 'L2',
            idType: 'Int64',
            autoID: false,
            primaryFieldName: 'id',
            vectorFieldName: 'vector',
            params: ['enableDynamicField' => true],
            description: '',
        ),
        '/v2/vectordb/collections/create',
        [
            'collectionName' => 'documents',
            'dimension' => 128,
            'dbName' => 'default',
            'metricType' => 'L2',
            'idType' => 'Int64',
            'autoID' => false,
            'primaryFieldName' => 'id',
            'vectorFieldName' => 'vector',
            'params' => ['enableDynamicField' => true],
            'description' => '',
        ],
    ],
    'create collection (custom setup)' => [
        new CreateCollection(
            collectionName: 'documents',
            dbName: 'default',
            schema: ['autoID' => false, 'fields' => []],
            indexParams: [['fieldName' => 'vector', 'metricType' => 'L2']],
            params: ['consistencyLevel' => 'Strong'],
        ),
        '/v2/vectordb/collections/create',
        [
            'collectionName' => 'documents',
            'dbName' => 'default',
            'schema' => ['autoID' => false, 'fields' => []],
            'indexParams' => [['fieldName' => 'vector', 'metricType' => 'L2']],
            'params' => ['consistencyLevel' => 'Strong'],
        ],
    ],
    'list collections' => [
        new ListCollections('default'),
        '/v2/vectordb/collections/list',
        ['dbName' => 'default'],
    ],
    'describe collection' => [
        new DescribeCollection('documents', 'default'),
        '/v2/vectordb/collections/describe',
        ['collectionName' => 'documents', 'dbName' => 'default'],
    ],
    'drop collection' => [
        new DropCollection('documents', 'default'),
        '/v2/vectordb/collections/drop',
        ['collectionName' => 'documents', 'dbName' => 'default'],
    ],
    'insert entities' => [
        new InsertVector('documents', [['id' => 1]], 'default', 'partition', false),
        '/v2/vectordb/entities/insert',
        [
            'data' => [['id' => 1]],
            'collectionName' => 'documents',
            'dbName' => 'default',
            'partitionName' => 'partition',
            'partialUpdate' => false,
        ],
    ],
    'upsert entities' => [
        new UpsertVector('documents', [['id' => 1]], 'default', 'partition', true),
        '/v2/vectordb/entities/upsert',
        [
            'data' => [['id' => 1]],
            'collectionName' => 'documents',
            'dbName' => 'default',
            'partitionName' => 'partition',
            'partialUpdate' => true,
        ],
    ],
    'get entities' => [
        new GetVector([1, 2], 'documents', ['title'], 'default', ['partition'], 'Strong', 'partition'),
        '/v2/vectordb/entities/get',
        [
            'id' => [1, 2],
            'collectionName' => 'documents',
            'outputFields' => ['title'],
            'dbName' => 'default',
            'partitionNames' => ['partition'],
            'consistencyLevel' => 'Strong',
            'partitionName' => 'partition',
        ],
    ],
    'query entities' => [
        new QueryVector('documents', '', 0, 0, ['title'], 'default', ['partition'], ['id' => 1], 'Strong'),
        '/v2/vectordb/entities/query',
        [
            'collectionName' => 'documents',
            'filter' => '',
            'limit' => 0,
            'offset' => 0,
            'outputFields' => ['title'],
            'dbName' => 'default',
            'partitionNames' => ['partition'],
            'exprParams' => ['id' => 1],
            'consistencyLevel' => 'Strong',
        ],
    ],
    'search entities' => [
        new SearchVector(
            collectionName: 'documents',
            data: [[0.1, 0.2]],
            annsField: 'vector',
            filter: '',
            limit: 0,
            offset: 0,
            groupingField: 'category',
            groupSize: 1,
            strictGroupSize: false,
            outputFields: ['title'],
            searchParams: ['metricType' => 'L2'],
            dbName: 'default',
            partitionNames: ['partition'],
            consistencyLevel: 'Strong',
            exprParams: ['category' => 'docs'],
            functionScore: ['name' => 'score'],
            params: ['ef' => 64],
        ),
        '/v2/vectordb/entities/search',
        [
            'collectionName' => 'documents',
            'data' => [[0.1, 0.2]],
            'annsField' => 'vector',
            'filter' => '',
            'limit' => 0,
            'offset' => 0,
            'groupingField' => 'category',
            'groupSize' => 1,
            'strictGroupSize' => false,
            'outputFields' => ['title'],
            'searchParams' => ['metricType' => 'L2'],
            'dbName' => 'default',
            'partitionNames' => ['partition'],
            'consistencyLevel' => 'Strong',
            'exprParams' => ['category' => 'docs'],
            'functionScore' => ['name' => 'score'],
            'params' => ['ef' => 64],
        ],
    ],
    'search entities by ID' => [
        new SearchVector(
            collectionName: 'documents',
            data: null,
            annsField: 'vector',
            outputFields: ['title'],
            ids: [1, '2'],
        ),
        '/v2/vectordb/entities/search',
        [
            'collectionName' => 'documents',
            'annsField' => 'vector',
            'outputFields' => ['title'],
            'ids' => [1, '2'],
        ],
    ],
    'delete entities' => [
        new DeleteVector('documents', '', 'default', 'partition', 0, ['id' => 1]),
        '/v2/vectordb/entities/delete',
        [
            'collectionName' => 'documents',
            'filter' => '',
            'dbName' => 'default',
            'partitionName' => 'partition',
            'id' => 0,
            'exprParams' => ['id' => 1],
        ],
    ],
]);

it('encodes empty list request bodies as JSON objects', function (Request $request) {
    expect((string) $request->body())->toBe('{}');
})->with([
    'databases' => new ListDatabases,
    'collections' => new ListCollections,
]);

it('supports authenticated and unauthenticated Milvus servers', function () {
    $authenticated = new Milvus('root:Milvus', 'localhost', '19530');
    $unauthenticated = new Milvus(null, 'localhost', '19530');
    $emptyToken = new Milvus('', 'localhost', '19530');

    expect($authenticated->getAuthenticator())->toBeInstanceOf(TokenAuthenticator::class)
        ->and($authenticated->getAuthenticator()->token)->toBe('root:Milvus')
        ->and($unauthenticated->getAuthenticator())->toBeNull()
        ->and($emptyToken->getAuthenticator())->toBeNull();
});

it('builds the API token from configured credentials', function () {
    config()->set('milvus.token', '');
    config()->set('milvus.username', 'root');
    config()->set('milvus.password', 'Milvus');

    $authenticator = $this->app->make(Milvus::class)->getAuthenticator();

    expect($authenticator)->toBeInstanceOf(TokenAuthenticator::class)
        ->and($authenticator->token)->toBe('root:Milvus');
});

it('prefers an explicitly configured API token', function () {
    config()->set('milvus.token', 'explicit-token');
    config()->set('milvus.username', 'root');
    config()->set('milvus.password', 'Milvus');

    $authenticator = $this->app->make(Milvus::class)->getAuthenticator();

    expect($authenticator)->toBeInstanceOf(TokenAuthenticator::class)
        ->and($authenticator->token)->toBe('explicit-token');
});

it('does not fabricate an API token when credentials are absent', function () {
    config()->set('milvus.token', null);
    config()->set('milvus.username', 'root');
    config()->set('milvus.password', null);

    expect($this->app->make(Milvus::class)->getAuthenticator())->toBeNull();
});
