<?php

use HelgeSverre\Milvus\Milvus;

beforeEach(function () {
    $this->milvus = new Milvus(
        token: env('MILVUS_TOKEN', ''),
        host: env('MILVUS_HOST', 'localhost'),
        port: env('MILVUS_PORT', '19530'),
    );
});

it('creates a collection and confirms if it exists in the list', function () {

    $this->milvus->collections()->drop('test_collection');

    $response = $this->milvus->collections()->create(
        collectionName: 'test_collection',
        dimension: 128,
    );

    // Status 0 and empty data is a success response
    expect($response->status())->toEqual(200);
    expect($response->json('code'))->toEqual(0);
    expect($response->json('data'))->toBeEmpty();

    sleep(1);

    $response = $this->milvus->collections()->list();

    expect($response->collect('data'))->toContain('test_collection');

    $response = $this->milvus->collections()->drop(collectionName: 'test_collection');

    // Status 0 and empty data is a success response
    expect($response->status())->toEqual(200);
    expect($response->json('code'))->toEqual(0);
    expect($response->json('data'))->toBeEmpty();

    sleep(1);

    $response = $this->milvus->collections()->list();

    expect($response->collect('data'))->not->toContain('test_collection');

});

it('can insert stuff into collections', function () {

    $this->milvus->collections()->create(
        collectionName: 'add_stuff_into_collections',
        dimension: 128,
    );

    $insert = $this->milvus->vector()->insert(
        collectionName: 'add_stuff_into_collections',
        data: [
            ['id' => 1, 'vector' => createTestVector(0.1)],
            ['id' => 2, 'vector' => createTestVector(0.2)],
            ['id' => 3, 'vector' => createTestVector(0.3)],
        ],
    );

    $insertedIds = $insert->collect('data.insertIds')->join(',');

    expect($insert->collect('data.insertIds')->count())->toEqual(3);

    sleep(1);

    $query = $this->milvus->vector()->query(
        collectionName: 'add_stuff_into_collections',
        filter: "id in [$insertedIds]",
    );

    expect($query->collect('data')->count())->toEqual(3);

});

it('can insert additional fields into a collection', function () {

    $this->milvus->collections()->create(
        collectionName: 'add_stuff_into_collections',
        dimension: 128,
    );

    $insert = $this->milvus->vector()->insert(
        collectionName: 'add_stuff_into_collections',
        data: [
            ['id' => 1, 'vector' => createTestVector(0.1), 'title' => 'untitled document'],
            ['id' => 2, 'vector' => createTestVector(0.2), 'title' => 'lorem ipsum,'],
            ['id' => 3, 'vector' => createTestVector(0.3), 'title' => 'i am a title that has content'],
        ],
    );

    $insertedIds = $insert->collect('data.insertIds')->join(',');

    expect($insert->collect('data.insertIds')->count())->toEqual(3);

    sleep(1);

    $query = $this->milvus->vector()->query(
        collectionName: 'add_stuff_into_collections',
        filter: "id in [$insertedIds]",
    );

    expect($query->collect('data')->count())->toEqual(3);

    $items = $query->collect('data');

    expect($items[0]['title'])->toEqual('untitled document')
        ->and($items[1]['title'])->toEqual('lorem ipsum,')
        ->and($items[2]['title'])->toEqual('i am a title that has content');

});

it('can search by vector and get the correct item back', function () {

    $this->milvus->collections()->drop('collection_test');

    $this->milvus->collections()->create(
        collectionName: 'collection_test',
        dimension: 128,
        // L2 sets the distance between 0 and ∞ (0 is the closest)
        metricType: 'L2',
    );

    $insert = $this->milvus->vector()->insert(
        collectionName: 'collection_test',
        data: [
            ['id' => 1, 'vector' => createTestVector(0.1), 'title' => 'untitled document'],
            ['id' => 2, 'vector' => createTestVector(0.2), 'title' => 'lorem ipsum,'],
            ['id' => 3, 'vector' => createTestVector(0.3), 'title' => 'i am a title that has content'],
        ],
    );

    sleep(1);

    $query = $this->milvus->vector()->search(
        collectionName: 'collection_test',
        data: [createTestVector(0.1)],
        annsField: 'vector',
        limit: 1,
        outputFields: ['title'],
    );

    $item = $query->collect('data')->first();

    expect($query->collect('data')->count())->toEqual(1)
        ->and($item['title'])->toEqual('untitled document')
        ->and($item['distance'])->toEqual(0);

});
