<?php

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\CollectionListResponse;
use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Data\Response\EntityResponse;
use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Data\Response\SearchResponse;
use HelgeSverre\Milvus\Milvus;

it('decodes a live Milvus lifecycle into typed response DTOs', function () {
    $milvus = $this->app->make(Milvus::class);
    $collectionName = 'php_dto_'.bin2hex(random_bytes(6));
    $created = false;

    try {
        $create = $milvus->collections()->create(
            collectionName: $collectionName,
            dimension: 4,
            metricType: 'COSINE',
            autoID: false,
        )->dto();
        $create->throwIfFailed();
        $created = true;

        expect($create)->toBeInstanceOf(EmptyResponse::class)
            ->and($create->data)->toBe([]);

        $list = $milvus->collections()->list()->dto()->throwIfFailed();
        $describe = $milvus->collections()->describe($collectionName)->dto()->throwIfFailed();

        expect($list)->toBeInstanceOf(CollectionListResponse::class)
            ->and($list->collections)->toContain($collectionName)
            ->and($describe)->toBeInstanceOf(CollectionDescriptionResponse::class)
            ->and($describe->collection->collectionName)->toBe($collectionName)
            ->and($describe->collection->fields)->toHaveCount(2)
            ->and($describe->collection->fields[0]->name)->toBe('id')
            ->and($describe->collection->fields[1]->name)->toBe('vector');

        $insert = $milvus->vector()->insert(
            collectionName: $collectionName,
            data: [
                ['id' => 1, 'vector' => [1.0, 0.0, 0.0, 0.0], 'title' => 'first'],
                ['id' => 2, 'vector' => [0.0, 1.0, 0.0, 0.0], 'title' => 'second'],
            ],
        )->dto()->throwIfFailed();

        expect($insert)->toBeInstanceOf(MutationResponse::class)
            ->and($insert->result->insertCount)->toBe(2)
            ->and($insert->result->insertIds)->toHaveCount(2);

        sleep(1);

        $get = $milvus->vector()->get(
            id: [1, 2],
            collectionName: $collectionName,
            outputFields: ['title'],
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();
        $query = $milvus->vector()->query(
            collectionName: $collectionName,
            filter: 'id in [1, 2]',
            outputFields: ['title'],
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();
        $search = $milvus->vector()->search(
            collectionName: $collectionName,
            data: [[1.0, 0.0, 0.0, 0.0]],
            annsField: 'vector',
            limit: 1,
            outputFields: ['title'],
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();

        expect($get)->toBeInstanceOf(EntityResponse::class)
            ->and($get->entities)->toHaveCount(2)
            ->and($query)->toBeInstanceOf(EntityResponse::class)
            ->and($query->entities)->toHaveCount(2)
            ->and($search)->toBeInstanceOf(SearchResponse::class)
            ->and($search->entities)->toHaveCount(1)
            ->and($search->entities[0]->id)->toBeIn([1, '1'])
            ->and($search->entities[0]->distance)->toEqual(1)
            ->and($search->entities[0]->field('title'))->toBe('first');

        $upsert = $milvus->vector()->upsert(
            collectionName: $collectionName,
            data: [['id' => 2, 'vector' => [0.0, 1.0, 0.0, 0.0], 'title' => 'updated']],
        )->dto()->throwIfFailed();
        $delete = $milvus->vector()->delete(
            collectionName: $collectionName,
            filter: 'id == 1',
        )->dto()->throwIfFailed();

        expect($upsert)->toBeInstanceOf(MutationResponse::class)
            ->and($upsert->result->upsertCount)->toBe(1)
            ->and($delete)->toBeInstanceOf(MutationResponse::class)
            ->and($delete->result->deleteCount)->toBe(1);
    } finally {
        if ($created) {
            $drop = $milvus->collections()->drop($collectionName)->dto()->throwIfFailed();

            expect($drop)->toBeInstanceOf(EmptyResponse::class);
        }
    }
});
