<?php

use HelgeSverre\Milvus\Milvus;

beforeEach(function () {
    $this->milvus = new Milvus('', 'localhost', '19530');
});

it('creates a collection and confirms if it exists in the list', function () {

    $response = $this->milvus->collections()->create(
        collectionName: 'test_collection',
        dimension: 128,
    );

    expect($response->json('code'))->toEqual(200);

    sleep(1);

    $response = $this->milvus->collections()->list();

    expect($response->collect('data'))->toContain('test_collection');

    $response = $this->milvus->collections()->drop(collectionName: 'test_collection');
    expect($response->json('code'))->toEqual(200);

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
            ['vector' => createTestVector(0.1)],
            ['vector' => createTestVector(0.2)],
            ['vector' => createTestVector(0.3)],
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
            ['vector' => createTestVector(0.1), 'title' => 'untitled document'],
            ['vector' => createTestVector(0.2), 'title' => 'lorem ipsum,'],
            ['vector' => createTestVector(0.3), 'title' => 'i am a title that has content'],
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
    );

    $insert = $this->milvus->vector()->insert(
        collectionName: 'collection_test',
        data: [
            ['vector' => createTestVector(0.1), 'title' => 'untitled document'],
            ['vector' => createTestVector(0.2), 'title' => 'lorem ipsum,'],
            ['vector' => createTestVector(0.3), 'title' => 'i am a title that has content'],
        ],
    );

    sleep(1);

    $query = $this->milvus->vector()->search(
        collectionName: 'collection_test',
        vector: createTestVector(0.1),
        limit: 1,
        outputFields: ['title'],
    );

    $items = $query->collect('data')->first();

    expect($query->collect('data')->count())->toEqual(1)
        ->and($items['title'])->toEqual('untitled document')
        ->and($items['distance'])->toEqual(0);

});
