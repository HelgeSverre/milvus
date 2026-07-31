<?php

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\CollectionListResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseListResponse;
use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Data\Response\EntityResponse;
use HelgeSverre\Milvus\Data\Response\MilvusResponse;
use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Data\Response\SearchResponse;
use HelgeSverre\Milvus\Exceptions\InvalidResponseException;
use HelgeSverre\Milvus\Exceptions\MilvusApiException;
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Requests\CollectionOperations\CreateCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DescribeCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\DropCollection;
use HelgeSverre\Milvus\Requests\CollectionOperations\ListCollections;
use HelgeSverre\Milvus\Requests\DatabaseOperations\CreateDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\DescribeDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\DropDatabase;
use HelgeSverre\Milvus\Requests\DatabaseOperations\ListDatabases;
use HelgeSverre\Milvus\Requests\MilvusRequest;
use HelgeSverre\Milvus\Requests\VectorOperations\DeleteVector;
use HelgeSverre\Milvus\Requests\VectorOperations\GetVector;
use HelgeSverre\Milvus\Requests\VectorOperations\InsertVector;
use HelgeSverre\Milvus\Requests\VectorOperations\QueryVector;
use HelgeSverre\Milvus\Requests\VectorOperations\SearchVector;
use HelgeSverre\Milvus\Requests\VectorOperations\UpsertVector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function decodeMilvusResponse(
    MilvusRequest $request,
    array|string $body,
    int $status = 200,
): MilvusResponse {
    $client = new Milvus(null, 'http://localhost', '19530');
    $client->withMockClient(new MockClient([
        MockResponse::make($body, $status),
    ]));

    return $client->send($request)->dto();
}

it('maps every request to its spec-defined response DTO', function (
    MilvusRequest $request,
    array $body,
    string $expectedDto,
) {
    expect(decodeMilvusResponse($request, $body))->toBeInstanceOf($expectedDto);
})->with([
    'create database' => [
        new CreateDatabase('analytics'),
        ['code' => 0, 'data' => []],
        EmptyResponse::class,
    ],
    'drop database' => [
        new DropDatabase('analytics'),
        ['code' => 0, 'data' => []],
        EmptyResponse::class,
    ],
    'list databases' => [
        new ListDatabases,
        ['code' => 0, 'data' => []],
        DatabaseListResponse::class,
    ],
    'describe database' => [
        new DescribeDatabase('analytics'),
        ['code' => 0, 'data' => ['dbName' => 'analytics']],
        DatabaseDescriptionResponse::class,
    ],
    'create collection' => [
        new CreateCollection('documents'),
        ['code' => 0, 'data' => []],
        EmptyResponse::class,
    ],
    'drop collection' => [
        new DropCollection('documents'),
        ['code' => 0, 'data' => []],
        EmptyResponse::class,
    ],
    'list collections' => [
        new ListCollections,
        ['code' => 0, 'data' => []],
        CollectionListResponse::class,
    ],
    'describe collection' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents']],
        CollectionDescriptionResponse::class,
    ],
    'insert entities' => [
        new InsertVector('documents', []),
        ['code' => 0, 'data' => []],
        MutationResponse::class,
    ],
    'upsert entities' => [
        new UpsertVector('documents', []),
        ['code' => 0, 'data' => []],
        MutationResponse::class,
    ],
    'delete entities' => [
        new DeleteVector('documents'),
        ['code' => 0, 'data' => []],
        MutationResponse::class,
    ],
    'get entities' => [
        new GetVector(1, 'documents'),
        ['code' => 0, 'data' => []],
        EntityResponse::class,
    ],
    'query entities' => [
        new QueryVector('documents'),
        ['code' => 0, 'data' => []],
        EntityResponse::class,
    ],
    'search entities' => [
        new SearchVector('documents', [[0.1, 0.2]], 'vector'),
        ['code' => 0, 'data' => []],
        SearchResponse::class,
    ],
]);

it('decodes database lists and descriptions without losing int64 or future fields', function () {
    $list = decodeMilvusResponse(new ListDatabases, [
        'code' => 0,
        'data' => ['default', 'analytics'],
    ]);
    $describe = decodeMilvusResponse(
        new DescribeDatabase('analytics'),
        '{"code":0,"data":{"dbName":"analytics","dbID":18446744073709551615,"properties":[{"key":"timezone","value":"UTC","future":true}],"futureDatabaseField":{"enabled":true}}}',
    );

    expect($list)->toBeInstanceOf(DatabaseListResponse::class)
        ->and($list->databases)->toBe(['default', 'analytics'])
        ->and($describe)->toBeInstanceOf(DatabaseDescriptionResponse::class)
        ->and($describe->database->dbName)->toBe('analytics')
        ->and($describe->database->dbId)->toBe('18446744073709551615')
        ->and($describe->database->properties[0]->key)->toBe('timezone')
        ->and($describe->database->properties[0]->value)->toBe('UTC')
        ->and($describe->database->properties[0]->raw['future'])->toBeTrue()
        ->and($describe->database->raw['futureDatabaseField'])->toBe(['enabled' => true]);
});

it('decodes collection lists and operation metadata', function () {
    $dto = decodeMilvusResponse(new ListCollections, [
        'code' => 0,
        'cost' => 3,
        'data' => ['documents', 'archive'],
    ]);

    expect($dto)->toBeInstanceOf(CollectionListResponse::class)
        ->and($dto->successful())->toBeTrue()
        ->and($dto->cost)->toBe(3)
        ->and($dto->collections)->toBe(['documents', 'archive'])
        ->and($dto->throwIfFailed())->toBe($dto);
});

it('decodes a Milvus 2.5 collection description including legacy shape variations', function () {
    $dto = decodeMilvusResponse(new DescribeCollection('documents'), [
        'code' => 0,
        'data' => [
            'aliases' => ['docs'],
            'autoId' => false,
            'collectionID' => 452987654321,
            'collectionName' => 'documents',
            'consistencyLevel' => 'Bounded',
            'description' => 'Document vectors',
            'enableDynamicField' => true,
            'fields' => [
                [
                    'name' => 'id',
                    'primaryKey' => true,
                    'autoId' => false,
                    'type' => 'Int64',
                    'id' => 100,
                    'params' => [],
                ],
                [
                    'name' => 'vector',
                    'primaryKey' => false,
                    'autoId' => false,
                    'type' => 'FloatVector',
                    'id' => 101,
                    'params' => [
                        ['key' => 'dim', 'value' => '2'],
                    ],
                ],
            ],
            'indexes' => [[
                'indexName' => 'vector',
                'fieldName' => 'vector',
                'metricType' => 'COSINE',
            ]],
            'load' => 'LoadStateLoaded',
            'partitionNum' => 1,
            'properties' => [[
                'key' => 'collection.ttl.seconds',
                'value' => '0',
            ]],
            'shardsNum' => 1,
        ],
    ]);

    expect($dto)->toBeInstanceOf(CollectionDescriptionResponse::class)
        ->and($dto->collection->collectionName)->toBe('documents')
        ->and($dto->collection->autoId)->toBeFalse()
        ->and($dto->collection->collectionId)->toBe(452987654321)
        ->and($dto->collection->partitionsNum)->toBe(1)
        ->and($dto->collection->fields)->toHaveCount(2)
        ->and($dto->collection->fields[0]->id)->toBe(100)
        ->and($dto->collection->fields[1]->params[0]['key'])->toBe('dim')
        ->and($dto->collection->indexes[0]->metricType)->toBe('COSINE')
        ->and($dto->collection->properties[0]->key)->toBe('collection.ttl.seconds');
});

it('decodes the current collection schema with functions and preserves future fields', function () {
    $dto = decodeMilvusResponse(new DescribeCollection('documents'), [
        'code' => 0,
        'data' => [
            'aliases' => [],
            'autoID' => true,
            'collectionID' => 9223372036854775807,
            'collectionName' => 'documents',
            'enableDynamicField' => true,
            'fields' => [[
                'name' => 'embedding',
                'type' => 'FloatVector',
                'id' => '101',
                'clusteringKey' => false,
                'nullable' => true,
                'defaultValue' => '[]',
                'isFunctionOutput' => true,
                'params' => ['dim' => '2'],
                'elementType' => 'Float',
                'futureFieldOption' => 'retained',
            ]],
            'functions' => [[
                'name' => 'embed_text',
                'description' => 'Build embeddings',
                'type' => 'TextEmbedding',
                'id' => '7',
                'inputFieldNames' => ['text'],
                'outputFieldNames' => ['embedding'],
                'params' => ['provider' => 'tei'],
            ]],
            'indexes' => [],
            'load' => 'LoadStateLoaded',
            'partitionsNum' => 2,
            'properties' => [],
            'externalSource' => '{"url":"s3://bucket/data"}',
            'externalSpec' => '{"format":"parquet"}',
            'futureCollectionOption' => ['enabled' => true],
        ],
    ]);

    expect($dto)->toBeInstanceOf(CollectionDescriptionResponse::class)
        ->and($dto->collection->autoId)->toBeTrue()
        ->and($dto->collection->collectionId)->toBe(9223372036854775807)
        ->and($dto->collection->partitionsNum)->toBe(2)
        ->and($dto->collection->functions[0]->inputFieldNames)->toBe(['text'])
        ->and($dto->collection->fields[0]->id)->toBe('101')
        ->and($dto->collection->fields[0]->params)->toBe(['dim' => '2'])
        ->and($dto->collection->fields[0]->raw['futureFieldOption'])->toBe('retained')
        ->and($dto->collection->raw['futureCollectionOption'])->toBe(['enabled' => true]);
});

it('preserves int64 values that exceed the local integer range as strings', function () {
    $dto = decodeMilvusResponse(
        new DescribeCollection('documents'),
        '{"code":0,"data":{"collectionName":"documents","collectionID":18446744073709551615}}',
    );

    expect($dto)->toBeInstanceOf(CollectionDescriptionResponse::class)
        ->and($dto->collection->collectionId)->toBe('18446744073709551615');
});

it('decodes insert, upsert, and live delete result variations', function (
    MilvusRequest $request,
    array $data,
    int $expectedCount,
    array $expectedIds,
) {
    $dto = decodeMilvusResponse($request, ['code' => 0, 'data' => $data]);

    expect($dto)->toBeInstanceOf(MutationResponse::class)
        ->and($dto->result->affectedCount())->toBe($expectedCount);

    $actualIds = $dto->result->insertIds ?: ($dto->result->upsertIds ?: $dto->result->deleteIds);
    expect($actualIds)->toBe($expectedIds);
})->with([
    'insert IDs default to strings' => [
        new InsertVector('documents', []),
        ['insertCount' => 2, 'insertIds' => ['1', '2']],
        2,
        ['1', '2'],
    ],
    'upsert IDs may be integers when explicitly requested' => [
        new UpsertVector('documents', []),
        ['upsertCount' => 2, 'upsertIds' => [1, 2]],
        2,
        [1, 2],
    ],
    'live Milvus delete response includes a count' => [
        new DeleteVector('documents'),
        ['deleteCount' => 1],
        1,
        [],
    ],
]);

it('accepts the empty delete result documented by the current OpenAPI schema', function () {
    $dto = decodeMilvusResponse(new DeleteVector('documents'), ['code' => 0, 'data' => []]);

    expect($dto)->toBeInstanceOf(MutationResponse::class)
        ->and($dto->result->affectedCount())->toBeNull()
        ->and($dto->result->raw)->toBe([]);
});

it('decodes arbitrary get and query entities without losing dynamic fields', function () {
    $dto = decodeMilvusResponse(new GetVector(1, 'documents'), [
        'code' => 0,
        'cost' => 2,
        'scanned_remote_bytes' => 512,
        'scanned_total_bytes' => 1024,
        'cache_hit_ratio' => 0.5,
        'data' => [[
            'id' => '9223372036854775807',
            'title' => 'Milvus',
            'nullable' => null,
            'metadata' => ['tags' => ['vector', 'database']],
            'vector' => [0.1, 0.2],
        ]],
    ]);

    expect($dto)->toBeInstanceOf(EntityResponse::class)
        ->and($dto->entities)->toHaveCount(1)
        ->and($dto->entities[0]->id)->toBe('9223372036854775807')
        ->and($dto->entities[0]->field('title'))->toBe('Milvus')
        ->and($dto->entities[0]->field('nullable', 'fallback'))->toBeNull()
        ->and($dto->entities[0]->toArray()['metadata']['tags'])->toBe(['vector', 'database'])
        ->and($dto->scannedRemoteBytes)->toBe(512)
        ->and($dto->scannedTotalBytes)->toBe(1024)
        ->and($dto->cacheHitRatio)->toBe(0.5);
});

it('distinguishes present null dynamic fields from missing fields', function () {
    $dto = decodeMilvusResponse(new QueryVector('documents'), [
        'code' => 0,
        'data' => [['id' => 1, 'nullable' => null]],
    ]);

    expect(array_key_exists('nullable', $dto->entities[0]->raw))->toBeTrue()
        ->and(array_key_exists('missing', $dto->entities[0]->raw))->toBeFalse();
});

it('decodes search-specific metadata and result fields', function () {
    $dto = decodeMilvusResponse(new SearchVector('documents', [[0.1, 0.2]], 'vector'), [
        'code' => 0,
        'cost' => 4,
        'data' => [[
            'id' => 1,
            'distance' => 0.125,
            'title' => 'Nearest document',
        ]],
        'recalls' => [0.95],
        'topks' => [1],
        'scanned_remote_bytes' => 64,
        'scanned_total_bytes' => 128,
        'cache_hit_ratio' => 0.75,
    ]);

    expect($dto)->toBeInstanceOf(SearchResponse::class)
        ->and($dto->entities[0]->distance)->toBe(0.125)
        ->and($dto->entities[0]->field('title'))->toBe('Nearest document')
        ->and($dto->recalls)->toBe([0.95])
        ->and($dto->topks)->toBe([1])
        ->and($dto->scannedRemoteBytes)->toBe(64)
        ->and($dto->scannedTotalBytes)->toBe(128)
        ->and($dto->cacheHitRatio)->toBe(0.75);
});

it('treats a Milvus error envelope as failure even when HTTP status is 200', function () {
    $dto = decodeMilvusResponse(new ListCollections, [
        'code' => 1800,
        'message' => 'database not found',
    ]);

    expect($dto)->toBeInstanceOf(CollectionListResponse::class)
        ->and($dto->successful())->toBeFalse()
        ->and($dto->collections)->toBe([])
        ->and(fn () => $dto->throwIfFailed())
        ->toThrow(MilvusApiException::class, 'database not found');

    try {
        $dto->throwIfFailed();
    } catch (MilvusApiException $exception) {
        expect($exception->milvusCode)->toBe(1800)
            ->and($exception->getCode())->toBe(1800);
    }
});

it('does not parse endpoint data from failed envelopes', function (MilvusRequest $request, string $expectedDto) {
    $dto = decodeMilvusResponse($request, [
        'code' => 65535,
        'message' => 'operation failed',
        'data' => 'error details may use a non-success shape',
    ]);

    expect($dto)->toBeInstanceOf($expectedDto)
        ->and($dto->successful())->toBeFalse()
        ->and(fn () => $dto->throwIfFailed())
        ->toThrow(MilvusApiException::class, 'operation failed');
})->with([
    'empty response' => [new CreateCollection('documents'), EmptyResponse::class],
    'collection list' => [new ListCollections, CollectionListResponse::class],
    'collection description' => [
        new DescribeCollection('documents'),
        CollectionDescriptionResponse::class,
    ],
    'database list' => [new ListDatabases, DatabaseListResponse::class],
    'database description' => [
        new DescribeDatabase('analytics'),
        DatabaseDescriptionResponse::class,
    ],
    'mutation response' => [new InsertVector('documents', []), MutationResponse::class],
    'entity response' => [new QueryVector('documents'), EntityResponse::class],
    'search response' => [
        new SearchVector('documents', [[0.1]], 'vector'),
        SearchResponse::class,
    ],
]);

it('provides a useful fallback message when Milvus omits one', function () {
    $dto = decodeMilvusResponse(new ListCollections, ['code' => 5]);

    expect(fn () => $dto->throwIfFailed())
        ->toThrow(MilvusApiException::class, 'Milvus API request failed with code 5.');
});

it('rejects malformed JSON without leaking the response body', function () {
    expect(fn () => decodeMilvusResponse(new ListCollections, '{"code":0,"secret":"token"'))
        ->toThrow(InvalidResponseException::class, 'Milvus returned malformed JSON.');
});

it('rejects invalid response shapes at the exact failing path', function (
    MilvusRequest $request,
    array $body,
    string $path,
) {
    expect(fn () => decodeMilvusResponse($request, $body))
        ->toThrow(InvalidResponseException::class, $path);
})->with([
    'missing response code' => [
        new ListCollections,
        ['data' => []],
        '"code"',
    ],
    'string response code' => [
        new ListCollections,
        ['code' => '0', 'data' => []],
        '"code"',
    ],
    'boolean response code' => [
        new ListCollections,
        ['code' => true, 'data' => []],
        '"code"',
    ],
    'top-level lists are not response objects' => [
        new ListCollections,
        [],
        '"$"',
    ],
    'success missing data' => [
        new ListCollections,
        ['code' => 0],
        '"data"',
    ],
    'response data must be an array or object' => [
        new ListCollections,
        ['code' => 0, 'data' => 'documents'],
        '"data"',
    ],
    'collection list must be a list' => [
        new ListCollections,
        ['code' => 0, 'data' => ['name' => 'documents']],
        '"data"',
    ],
    'collection list entries must be strings' => [
        new ListCollections,
        ['code' => 0, 'data' => ['documents', 2]],
        '"data.1"',
    ],
    'database list must be a list' => [
        new ListDatabases,
        ['code' => 0, 'data' => ['name' => 'analytics']],
        '"data"',
    ],
    'database list entries must be strings' => [
        new ListDatabases,
        ['code' => 0, 'data' => ['default', 2]],
        '"data.1"',
    ],
    'database name is required' => [
        new DescribeDatabase('analytics'),
        ['code' => 0, 'data' => ['properties' => []]],
        '"data.dbName"',
    ],
    'database IDs must be integer or string' => [
        new DescribeDatabase('analytics'),
        ['code' => 0, 'data' => ['dbName' => 'analytics', 'dbID' => false]],
        '"data.dbID"',
    ],
    'database property entries must be objects' => [
        new DescribeDatabase('analytics'),
        ['code' => 0, 'data' => ['dbName' => 'analytics', 'properties' => ['bad']]],
        '"data.properties.0"',
    ],
    'database property keys are required' => [
        new DescribeDatabase('analytics'),
        ['code' => 0, 'data' => [
            'dbName' => 'analytics',
            'properties' => [['value' => 'UTC']],
        ]],
        '"data.properties.0.key"',
    ],
    'database property values must be strings' => [
        new DescribeDatabase('analytics'),
        ['code' => 0, 'data' => [
            'dbName' => 'analytics',
            'properties' => [['key' => 'database.replica.number', 'value' => 3]],
        ]],
        '"data.properties.0.value"',
    ],
    'collection name is required' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['fields' => []]],
        '"data.collectionName"',
    ],
    'auto ID must be boolean' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents', 'autoID' => 'true']],
        '"data.autoID"',
    ],
    'collection IDs must be integer or string' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents', 'collectionID' => false]],
        '"data.collectionID"',
    ],
    'load state must be a string' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents', 'load' => []]],
        '"data.load"',
    ],
    'external spec must be JSON encoded as a string' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents', 'externalSpec' => []]],
        '"data.externalSpec"',
    ],
    'field entries must be objects' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents', 'fields' => ['bad']]],
        '"data.fields.0"',
    ],
    'field type is required' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => ['collectionName' => 'documents', 'fields' => [['name' => 'id']]]],
        '"data.fields.0.type"',
    ],
    'field IDs reject floats' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'fields' => [['name' => 'id', 'type' => 'Int64', 'id' => 1.5]],
        ]],
        '"data.fields.0.id"',
    ],
    'field params must be an object or list' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'fields' => [['name' => 'id', 'type' => 'Int64', 'params' => 'bad']],
        ]],
        '"data.fields.0.params"',
    ],
    'field default values follow the string schema' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'fields' => [['name' => 'status', 'type' => 'VarChar', 'defaultValue' => 1]],
        ]],
        '"data.fields.0.defaultValue"',
    ],
    'function input names must be a list of strings' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'functions' => [[
                'name' => 'embed',
                'type' => 'TextEmbedding',
                'inputFieldNames' => ['text', 2],
            ]],
        ]],
        '"data.functions.0.inputFieldNames.1"',
    ],
    'function params must be an object' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'functions' => [['name' => 'embed', 'type' => 'TextEmbedding', 'params' => 'bad']],
        ]],
        '"data.functions.0.params"',
    ],
    'index field names are required' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'indexes' => [['indexName' => 'vector_idx']],
        ]],
        '"data.indexes.0.fieldName"',
    ],
    'collection properties require string values' => [
        new DescribeCollection('documents'),
        ['code' => 0, 'data' => [
            'collectionName' => 'documents',
            'properties' => [['key' => 'ttl', 'value' => 3600]],
        ]],
        '"data.properties.0.value"',
    ],
    'mutation counts must be integers' => [
        new InsertVector('documents', []),
        ['code' => 0, 'data' => ['insertCount' => '1']],
        '"data.insertCount"',
    ],
    'mutation IDs must be integer or string' => [
        new InsertVector('documents', []),
        ['code' => 0, 'data' => ['insertIds' => [['id' => 1]]]],
        '"data.insertIds.0"',
    ],
    'mutation IDs must be a list' => [
        new InsertVector('documents', []),
        ['code' => 0, 'data' => ['insertIds' => ['first' => '1']]],
        '"data.insertIds"',
    ],
    'entity data must be a list' => [
        new QueryVector('documents'),
        ['code' => 0, 'data' => ['id' => 1]],
        '"data"',
    ],
    'entity entries must be objects' => [
        new QueryVector('documents'),
        ['code' => 0, 'data' => [1]],
        '"data.0"',
    ],
    'canonical entity IDs reject floats' => [
        new GetVector(1, 'documents'),
        ['code' => 0, 'data' => [['id' => 1.5]]],
        '"data.0.id"',
    ],
    'search distances must be numbers' => [
        new SearchVector('documents', [[0.1]], 'vector'),
        ['code' => 0, 'data' => [['distance' => '0.1']]],
        '"data.0.distance"',
    ],
    'search recalls must be numbers' => [
        new SearchVector('documents', [[0.1]], 'vector'),
        ['code' => 0, 'data' => [], 'recalls' => ['0.9']],
        '"recalls.0"',
    ],
    'search topks must be integers' => [
        new SearchVector('documents', [[0.1]], 'vector'),
        ['code' => 0, 'data' => [], 'topks' => ['1']],
        '"topks.0"',
    ],
    'storage byte counts must be integers' => [
        new QueryVector('documents'),
        ['code' => 0, 'data' => [], 'scanned_total_bytes' => 1.5],
        '"scanned_total_bytes"',
    ],
    'operation cost must be an integer' => [
        new QueryVector('documents'),
        ['code' => 0, 'data' => [], 'cost' => 1.5],
        '"cost"',
    ],
    'cache hit ratio must be numeric' => [
        new QueryVector('documents'),
        ['code' => 0, 'data' => [], 'cache_hit_ratio' => '0.5'],
        '"cache_hit_ratio"',
    ],
]);
