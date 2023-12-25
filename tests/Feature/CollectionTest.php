<?php

use HelgeSverre\Milvus\Milvus;

beforeEach(function () {
    $this->milvus = new Milvus('', 'localhost', '19530');
});

it('confirms that listing collections in the db is empty', function () {

    $response = $this->milvus->collections()->list();

    dd($response->json());

});

it('creates a collection and confirms the list method returns 1 item', function () {

    $response = $this->milvus->collections()->create(
        collectionName: 'test_collection',
        dimension: 128,
    );

    $response = $this->milvus->collections()->list();

    dd($response->json());

});
