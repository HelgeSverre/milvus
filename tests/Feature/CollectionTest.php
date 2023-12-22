<?php

use Milvus\Milvus;
use Milvus\Requests\CollectionOperations\CreateCollection;
use Milvus\Requests\CollectionOperations\ListCollections;
use Saloon\Laravel\Facades\Saloon;

it('confirms that listing collections in the db is empty', function () {
    Saloon::fake([
        ListCollections::class => MockResponse::empty(200),
    ]);

    $milvus = new Milvus('your_token', 'your_host', 'your_port');
    Saloon::fake([
        ListCollections::class => MockResponse::empty(200),
    ]);

    Saloon::assertSent(CreateCollection::class);

    $response = $this->milvus->execute(new ListCollections());

    Saloon::assertSent(ListCollections::class);

    Saloon::assertSent(ListCollections::class);
    expect($response->getCollections())->toBeEmpty();
});

it('creates a collection and confirms the list method returns 1 item', function () {
    Saloon::fake([
        CreateCollection::class => MockResponse::empty(200),
        ListCollections::class => MockResponse::make(['collections' => ['test_collection']], 200),
    ]);

    $milvus = new Milvus('your_token', 'your_host', 'your_port');
    $createCollection = new CreateCollection('test_collection', 128);
    $milvus->execute($createCollection);

    Saloon::fake([
        ListCollections::class => MockResponse::empty(200),
    ]);

    Saloon::assertSent(ListCollections::class);
    expect($response->getCollections())->toHaveCount(1);
});
