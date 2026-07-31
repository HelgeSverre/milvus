<?php

use HelgeSverre\Milvus\Facades\Milvus as MilvusFacade;
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Requests\VectorOperations\SearchVector;
use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

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

it('forwards search arguments to the request', function () {
    $mockClient = new MockClient([
        SearchVector::class => MockResponse::make(['code' => 0], 200),
    ]);
    $milvus = new Milvus(null, 'localhost', '19530');
    $milvus->withMockClient($mockClient);

    $response = $milvus->vector()->search(
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
    );

    expect($response->status())->toBe(200)
        ->and($response->json('code'))->toBe(0);

    $mockClient->assertSent(
        fn (SearchVector $request): bool => $request->body()->all() === [
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
        ]
    );
});
