<?php

use HelgeSverre\Milvus\Milvus;

beforeEach(function () {
    $this->milvus = new Milvus('', 'localhost', '19530');
});

it('confirms that listing collections in the db is empty', function () {

    $response = $this->milvus->collections()->list();

//    dd($response->json());

});

it('creates a collection and confirms the list method returns 1 item', function () {

    $response = $this->milvus->collections()->create(
        collectionName: 'test_collection',
        dimension: 128,
    );

    expect($response->json('code'))->toEqual(200);


//    $response = $this->milvus->collections()->list();
//    expect($response->json('data'))->toHaveCount(1);


    $describe = $this->milvus->collections()->describe(collectionName: 'test_collection');
    expect($describe->json('code'))->toEqual(200);
    dd($describe->json());

    $response = $this->milvus->collections()->drop(collectionName: 'test_collection');
    expect($response->json('code'))->toEqual(200);

    $response = $this->milvus->collections()->list();
    expect($response->json('data'))->toHaveCount(0);

});

it('can insert stuff into collections', function () {

    $response = $this->milvus->collections()->create(
        collectionName: 'add_stuff_into_collections',
        dimension: 128,
    );


    $response = $this->milvus->vector()->insert(
        collectionName: 'add_stuff_into_collections',
        data: [
            "vector" => array_fill(0, 128, 0.1),
        ],
    );

    dump($response->json());

    $response = $this->milvus->vector()->query(
        collectionName: 'add_stuff_into_collections',
        filter: "id in [446564440140681987]",
    );



    dd($response->json());

});
