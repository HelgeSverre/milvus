<?php

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\CollectionListResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseListResponse;
use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Data\Response\EntityResponse;
use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Data\Response\SearchResponse;
use HelgeSverre\Milvus\Milvus;

it('smoke tests every supported operation against live Milvus', function () {
    $milvus = $this->app->make(Milvus::class);
    $suffix = bin2hex(random_bytes(6));
    $databaseName = 'php_smoke_'.$suffix;
    $collectionName = 'documents_'.$suffix;
    $databaseCreated = false;
    $collectionCreated = false;

    try {
        $initialDatabases = $milvus->databases()->list()->dto()->throwIfFailed();

        expect($initialDatabases)->toBeInstanceOf(DatabaseListResponse::class)
            ->and($initialDatabases->databases)->toContain('default')
            ->not->toContain($databaseName);

        $createdDatabase = $milvus->databases()->create(
            dbName: $databaseName,
            properties: ['database.replica.number' => 1],
        )->dto()->throwIfFailed();
        $databaseCreated = true;

        $databases = $milvus->databases()->list()->dto()->throwIfFailed();
        $database = $milvus->databases()->describe($databaseName)->dto()->throwIfFailed();
        $replicaProperty = collect($database->database->properties)
            ->firstWhere('key', 'database.replica.number');

        expect($createdDatabase)->toBeInstanceOf(EmptyResponse::class)
            ->and($databases)->toBeInstanceOf(DatabaseListResponse::class)
            ->and($databases->databases)->toContain($databaseName)
            ->and($database)->toBeInstanceOf(DatabaseDescriptionResponse::class)
            ->and($database->database->dbName)->toBe($databaseName)
            ->and($replicaProperty)->not->toBeNull()
            ->and($replicaProperty->value)->toBe('1');

        $initialCollections = $milvus->collections()->list($databaseName)->dto()->throwIfFailed();

        expect($initialCollections)->toBeInstanceOf(CollectionListResponse::class)
            ->and($initialCollections->collections)->not->toContain($collectionName);

        $createdCollection = $milvus->collections()->create(
            collectionName: $collectionName,
            dimension: 4,
            dbName: $databaseName,
            metricType: 'COSINE',
            autoID: false,
            description: 'End-to-end PHP client smoke test',
        )->dto()->throwIfFailed();
        $collectionCreated = true;

        $collections = $milvus->collections()->list($databaseName)->dto()->throwIfFailed();
        $collection = $milvus->collections()->describe($collectionName, $databaseName)->dto()->throwIfFailed();
        $fieldNames = collect($collection->collection->fields)->pluck('name')->all();

        expect($createdCollection)->toBeInstanceOf(EmptyResponse::class)
            ->and($collections)->toBeInstanceOf(CollectionListResponse::class)
            ->and($collections->collections)->toContain($collectionName)
            ->and($collection)->toBeInstanceOf(CollectionDescriptionResponse::class)
            ->and($collection->collection->collectionName)->toBe($collectionName)
            ->and($collection->collection->autoId)->toBeFalse()
            ->and($fieldNames)->toContain('id', 'vector');

        $vectors = [
            1 => [1.0, 0.0, 0.0, 0.0],
            2 => [0.0, 1.0, 0.0, 0.0],
            3 => [0.0, 0.0, 1.0, 0.0],
        ];
        $insert = $milvus->vector()->insert(
            collectionName: $collectionName,
            dbName: $databaseName,
            data: [
                ['id' => 1, 'vector' => $vectors[1], 'title' => 'alpha', 'project_id' => 10],
                ['id' => 2, 'vector' => $vectors[2], 'title' => 'beta', 'project_id' => 20],
                ['id' => 3, 'vector' => $vectors[3], 'title' => 'gamma', 'project_id' => 10],
            ],
        )->dto()->throwIfFailed();
        $insertIds = collect($insert->result->insertIds)
            ->map(static fn (int|string $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        expect($insert)->toBeInstanceOf(MutationResponse::class)
            ->and($insert->result->insertCount)->toBe(3)
            ->and($insertIds)->toBe(['1', '2', '3']);

        $get = $milvus->vector()->get(
            id: [1, 2],
            collectionName: $collectionName,
            outputFields: ['title', 'project_id'],
            dbName: $databaseName,
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();
        $getTitles = collect($get->entities)
            ->map(static fn ($entity): mixed => $entity->field('title'))
            ->sort()
            ->values()
            ->all();

        expect($get)->toBeInstanceOf(EntityResponse::class)
            ->and($get->entities)->toHaveCount(2)
            ->and($getTitles)->toBe(['alpha', 'beta']);

        $query = $milvus->vector()->query(
            collectionName: $collectionName,
            filter: 'project_id == 10',
            outputFields: ['title', 'project_id'],
            dbName: $databaseName,
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();
        $queriedIds = collect($query->entities)
            ->pluck('id')
            ->map(static fn (int|string $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        expect($query)->toBeInstanceOf(EntityResponse::class)
            ->and($query->entities)->toHaveCount(2)
            ->and($queriedIds)->toBe(['1', '3'])
            ->and(collect($query->entities)->map->field('project_id')->all())->each->toBe(10);

        $search = $milvus->vector()->search(
            collectionName: $collectionName,
            data: [$vectors[1]],
            annsField: 'vector',
            filter: 'project_id == 10',
            limit: 2,
            outputFields: ['title', 'project_id'],
            dbName: $databaseName,
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();

        expect($search)->toBeInstanceOf(SearchResponse::class)
            ->and($search->entities)->toHaveCount(2)
            ->and($search->entities[0]->id)->toBeIn([1, '1'])
            ->and($search->entities[0]->distance)->toEqual(1)
            ->and($search->entities[0]->field('title'))->toBe('alpha')
            ->and(collect($search->entities)->map->field('project_id')->all())->each->toBe(10);

        $upsert = $milvus->vector()->upsert(
            collectionName: $collectionName,
            dbName: $databaseName,
            data: [[
                'id' => 2,
                'vector' => $vectors[2],
                'title' => 'beta updated',
                'project_id' => 20,
            ]],
        )->dto()->throwIfFailed();
        $updated = $milvus->vector()->get(
            id: 2,
            collectionName: $collectionName,
            outputFields: ['title', 'project_id'],
            dbName: $databaseName,
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();

        expect($upsert)->toBeInstanceOf(MutationResponse::class)
            ->and($upsert->result->upsertCount)->toBe(1)
            ->and($updated->entities)->toHaveCount(1)
            ->and($updated->entities[0]->field('title'))->toBe('beta updated');

        if (str_starts_with((string) getenv('MILVUS_VERSION'), '3.')) {
            $partialUpdate = $milvus->vector()->upsert(
                collectionName: $collectionName,
                data: [['id' => 3, 'title' => 'gamma patched']],
                dbName: $databaseName,
                partialUpdate: true,
            )->dto()->throwIfFailed();
            $searchById = $milvus->vector()->search(
                collectionName: $collectionName,
                data: null,
                annsField: 'vector',
                limit: 1,
                outputFields: ['title'],
                dbName: $databaseName,
                consistencyLevel: 'Strong',
                ids: [3],
            )->dto()->throwIfFailed();

            expect($partialUpdate->result->upsertCount)->toBe(1)
                ->and($searchById)->toBeInstanceOf(SearchResponse::class)
                ->and($searchById->entities)->toHaveCount(1)
                ->and($searchById->entities[0]->id)->toBeIn([3, '3'])
                ->and($searchById->entities[0]->field('title'))->toBe('gamma patched');
        }

        $deleteById = $milvus->vector()->delete(
            collectionName: $collectionName,
            dbName: $databaseName,
            id: 1,
        )->dto()->throwIfFailed();
        $deleteByFilter = $milvus->vector()->delete(
            collectionName: $collectionName,
            filter: 'id == 2',
            dbName: $databaseName,
        )->dto()->throwIfFailed();
        $remaining = $milvus->vector()->query(
            collectionName: $collectionName,
            filter: 'id in [1, 2, 3]',
            outputFields: ['title'],
            dbName: $databaseName,
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();

        expect($deleteById)->toBeInstanceOf(MutationResponse::class)
            ->and($deleteById->result->deleteCount)->toBe(1)
            ->and($deleteByFilter)->toBeInstanceOf(MutationResponse::class)
            ->and($deleteByFilter->result->deleteCount)->toBe(1)
            ->and($remaining->entities)->toHaveCount(1)
            ->and($remaining->entities[0]->id)->toBeIn([3, '3']);

        $droppedCollection = $milvus->collections()->drop($collectionName, $databaseName)
            ->dto()
            ->throwIfFailed();
        $collectionCreated = false;

        expect($droppedCollection)->toBeInstanceOf(EmptyResponse::class)
            ->and($milvus->collections()->list($databaseName)->dto()->throwIfFailed()->collections)
            ->not->toContain($collectionName);

        $droppedDatabase = $milvus->databases()->drop($databaseName)->dto()->throwIfFailed();
        $databaseCreated = false;

        expect($droppedDatabase)->toBeInstanceOf(EmptyResponse::class)
            ->and($milvus->databases()->list()->dto()->throwIfFailed()->databases)
            ->not->toContain($databaseName);
    } finally {
        if ($collectionCreated) {
            $milvus->collections()->drop($collectionName, $databaseName)->dto()->throwIfFailed();
        }

        if ($databaseCreated) {
            $milvus->databases()->drop($databaseName)->dto()->throwIfFailed();
        }
    }
});
