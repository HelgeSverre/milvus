<?php

use HelgeSverre\Milvus\Facades\Milvus as MilvusFacade;
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Requests\CollectionOperations\CreateCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DescribeCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DropCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\ListCollections;
use HelgeSverre\Milvus\Requests\VectorOperations\DeleteVector;
use HelgeSverre\Milvus\Requests\VectorOperations\GetVector;
use HelgeSverre\Milvus\Requests\VectorOperations\InsertVector;
use HelgeSverre\Milvus\Requests\VectorOperations\QueryVector;
use HelgeSverre\Milvus\Requests\VectorOperations\SearchVector;
use HelgeSverre\Milvus\Requests\VectorOperations\UpsertVector;
use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('builds the configured base URL and exposes its resources', function () {
    $milvus = new Milvus(null, 'https://milvus.example.com', '443');

    expect($milvus->resolveBaseUrl())->toBe('https://milvus.example.com:443')
        ->and($milvus->collections())->toBeInstanceOf(CollectionOperations::class)
        ->and($milvus->vector())->toBeInstanceOf(VectorOperations::class);
});

it('resolves the configured client through the Laravel facade', function () {
    config()->set('milvus.host', 'https://milvus.example.com');
    config()->set('milvus.port', '443');
    MilvusFacade::clearResolvedInstance(Milvus::class);

    $milvus = MilvusFacade::getFacadeRoot();

    expect($milvus)->toBeInstanceOf(Milvus::class)
        ->and($milvus->resolveBaseUrl())->toBe('https://milvus.example.com:443');
});

it('forwards public resource arguments to the expected request', function (
    Closure $operation,
    string $requestClass,
    array $expectedBody,
) {
    $mockClient = new MockClient([
        $requestClass => MockResponse::make(['code' => 0], 200),
    ]);
    $milvus = new Milvus(null, 'localhost', '19530');
    $milvus->withMockClient($mockClient);

    $response = $operation($milvus);

    expect($response->status())->toBe(200)
        ->and($response->json('code'))->toBe(0);

    $mockClient->assertSent(
        fn (Request $request): bool => $request instanceof $requestClass
            && $request->body()->all() === $expectedBody
    );
    $mockClient->assertSentCount(1);
})->with([
    'list collections' => [
        fn (Milvus $milvus) => $milvus->collections()->list('analytics'),
        ListCollections::class,
        ['dbName' => 'analytics'],
    ],
    'create collection' => [
        fn (Milvus $milvus) => $milvus->collections()->create(
            collectionName: 'documents',
            dimension: 384,
            dbName: 'analytics',
            metricType: 'COSINE',
            idType: 'VarChar',
            autoID: false,
            primaryFieldName: 'document_id',
            vectorFieldName: 'embedding',
            schema: ['autoID' => false, 'fields' => []],
            indexParams: [['fieldName' => 'embedding']],
            params: ['consistencyLevel' => 'Strong'],
            description: '',
        ),
        CreateCollection::class,
        [
            'collectionName' => 'documents',
            'dimension' => 384,
            'dbName' => 'analytics',
            'metricType' => 'COSINE',
            'idType' => 'VarChar',
            'autoID' => false,
            'primaryFieldName' => 'document_id',
            'vectorFieldName' => 'embedding',
            'schema' => ['autoID' => false, 'fields' => []],
            'indexParams' => [['fieldName' => 'embedding']],
            'params' => ['consistencyLevel' => 'Strong'],
            'description' => '',
        ],
    ],
    'describe collection' => [
        fn (Milvus $milvus) => $milvus->collections()->describe('documents', 'analytics'),
        DescribeCollection::class,
        ['collectionName' => 'documents', 'dbName' => 'analytics'],
    ],
    'drop collection' => [
        fn (Milvus $milvus) => $milvus->collections()->drop('documents', 'analytics'),
        DropCollection::class,
        ['collectionName' => 'documents', 'dbName' => 'analytics'],
    ],
    'insert entities' => [
        fn (Milvus $milvus) => $milvus->vector()->insert(
            collectionName: 'documents',
            data: [['id' => 1]],
            dbName: 'analytics',
            partitionName: 'published',
            partialUpdate: false,
        ),
        InsertVector::class,
        [
            'data' => [['id' => 1]],
            'collectionName' => 'documents',
            'dbName' => 'analytics',
            'partitionName' => 'published',
            'partialUpdate' => false,
        ],
    ],
    'upsert entities' => [
        fn (Milvus $milvus) => $milvus->vector()->upsert(
            collectionName: 'documents',
            data: [['id' => 1, 'title' => 'Updated']],
            dbName: 'analytics',
            partitionName: 'published',
            partialUpdate: true,
        ),
        UpsertVector::class,
        [
            'data' => [['id' => 1, 'title' => 'Updated']],
            'collectionName' => 'documents',
            'dbName' => 'analytics',
            'partitionName' => 'published',
            'partialUpdate' => true,
        ],
    ],
    'get entities' => [
        fn (Milvus $milvus) => $milvus->vector()->get(
            id: [1, 2],
            collectionName: 'documents',
            outputFields: ['title'],
            dbName: 'analytics',
            partitionNames: ['published'],
            consistencyLevel: 'Strong',
            partitionName: 'published',
        ),
        GetVector::class,
        [
            'id' => [1, 2],
            'collectionName' => 'documents',
            'outputFields' => ['title'],
            'dbName' => 'analytics',
            'partitionNames' => ['published'],
            'consistencyLevel' => 'Strong',
            'partitionName' => 'published',
        ],
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
            exprParams: ['category' => 'docs'],
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
            'exprParams' => ['category' => 'docs'],
            'consistencyLevel' => 'Strong',
        ],
    ],
    'search entities by vector' => [
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
            exprParams: ['category' => 'docs'],
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
            'exprParams' => ['category' => 'docs'],
            'functionScore' => ['name' => 'rerank'],
            'params' => ['ef' => 64],
        ],
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
    ],
    'delete entities' => [
        fn (Milvus $milvus) => $milvus->vector()->delete(
            collectionName: 'documents',
            filter: '',
            dbName: 'analytics',
            partitionName: 'published',
            id: 0,
            exprParams: ['documentId' => 1],
        ),
        DeleteVector::class,
        [
            'collectionName' => 'documents',
            'filter' => '',
            'dbName' => 'analytics',
            'partitionName' => 'published',
            'id' => 0,
            'exprParams' => ['documentId' => 1],
        ],
    ],
]);
