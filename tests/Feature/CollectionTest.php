<?php

use HelgeSverre\Milvus\Milvus;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

beforeEach(function () {
    // Restart the Docker Compose system before each test
    //    restartDockerServices();
    $this->milvus = new Milvus('', 'localhost', '19530');
});

/**
 * Restart Docker services using docker-compose.
 */
function restartDockerServices()
{
    $path = realpath(__DIR__.'/../../');

    dump('Shutting down Docker services...');
    $processDown = new Process(['docker-compose', 'down']);
    $processDown->setWorkingDirectory($path);
    $processDown->run();

    if (! $processDown->isSuccessful()) {
        throw new ProcessFailedException($processDown);
    }

    dump('Starting Docker services...');
    $processUp = new Process(['docker-compose', 'up', '-d']);
    $processUp->setWorkingDirectory($path);
    $processUp->run();

    if (! $processUp->isSuccessful()) {
        throw new ProcessFailedException($processUp);
    }

    dump('Docker services restarted.');

    sleep(3);
}

it('creates a collection and confirms if it exists in the list', function () {

    // Create a new collection named 'test_collection'
    $response = $this->milvus->collections()->create(
        collectionName: 'test_collection',
        dimension: 128,
    );

    // Expect the creation to be successful
    expect($response->json('code'))->toEqual(200);

    // Sleep to ensure the collection is created
    sleep(1);

    // List all collections
    $response = $this->milvus->collections()->list();

    // Check if 'test_collection' is in the list of collections
    expect($response->collect('data'))->toContain('test_collection');

    // Drop the 'test_collection'
    $response = $this->milvus->collections()->drop(collectionName: 'test_collection');
    expect($response->json('code'))->toEqual(200);

    // Sleep to ensure the collection is dropped
    sleep(1);

    // List collections again to confirm 'test_collection' has been removed
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
            ['vector' => array_fill(0, 128, 0.1)],
            ['vector' => array_fill(0, 128, 0.2)],
            ['vector' => array_fill(0, 128, 0.3)],
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
            ['vector' => array_fill(0, 128, 0.1), 'title' => 'untitled document'],
            ['vector' => array_fill(0, 128, 0.2), 'title' => 'lorem ipsum,'],
            ['vector' => array_fill(0, 128, 0.3), 'title' => 'i am a title that has content'],
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
            ['vector' => array_fill(0, 128, 0.1), 'title' => 'untitled document'],
            ['vector' => array_fill(0, 128, 0.2), 'title' => 'lorem ipsum,'],
            ['vector' => array_fill(0, 128, 0.3), 'title' => 'i am a title that has content'],
        ],
    );

    sleep(1);

    $query = $this->milvus->vector()->search(
        collectionName: 'collection_test',
        vector: array_fill(0, 128, 0.1),
        limit: 1,
        outputFields: ['title'],
    );

    $items = $query->collect('data')->first();

    expect($query->collect('data')->count())->toEqual(1)
        ->and($items['title'])->toEqual('untitled document')
        ->and($items['distance'])->toEqual(0);

});
