<?php

use Milvus\Milvus;
use Milvus\Requests\CollectionOperations\ListCollections;
use Milvus\Requests\CollectionOperations\CreateCollection;

it('confirms that listing collections in the db is empty', function () {
    $milvus = new Milvus('your_token', 'your_host', 'your_port');
    Saloon::fake([
        ListCollections::class => MockResponse::empty(200),
    ]);

    $response = $this->milvus->execute(new ListCollections());

    Saloon::assertSent(ListCollections::class);
    expect($response->getCollections())->toBeEmpty();
});

it('creates a collection and confirms the list method returns 1 item', function () {
    $milvus = new Milvus('your_token', 'your_host', 'your_port');
    $createCollection = new CreateCollection('test_collection', 128);
    $milvus->execute($createCollection);

    Saloon::fake([
        ListCollections::class => MockResponse::empty(200),
    ]);

    expect($response->getCollections())->toHaveCount(1);
});
