<?php

use HelgeSverre\Milvus\Milvus;

it('supports the configured Milvus collection and vector lifecycle', function () {
    $milvus = $this->app->make(Milvus::class);
    $collectionName = 'php_client_'.bin2hex(random_bytes(6));
    $created = false;

    try {
        $create = $milvus->collections()->create(
            collectionName: $collectionName,
            dimension: 128,
            metricType: 'L2',
            autoID: false,
        );
        $created = $create->json('code') === 0;

        expect($create->status())->toBe(200)
            ->and($create->json('code'))->toBe(0);

        $list = $milvus->collections()->list();
        $describe = $milvus->collections()->describe($collectionName);

        expect($list->collect('data'))->toContain($collectionName)
            ->and($describe->json('code'))->toBe(0)
            ->and($describe->json('data.collectionName'))->toBe($collectionName);

        $insert = $milvus->vector()->insert(
            collectionName: $collectionName,
            data: [
                ['id' => 1, 'vector' => createTestVector(0.1), 'title' => 'first', 'project_id' => 10],
                ['id' => 2, 'vector' => createTestVector(0.2), 'title' => 'second', 'project_id' => 20],
                ['id' => 3, 'vector' => createTestVector(0.3), 'title' => 'third', 'project_id' => 10],
            ],
        );

        expect($insert->json('code'))->toBe(0)
            ->and($insert->json('data.insertCount'))->toBe(3);

        sleep(1);

        $get = $milvus->vector()->get(
            id: [1, 2],
            collectionName: $collectionName,
            outputFields: ['title'],
        );
        $query = $milvus->vector()->query(
            collectionName: $collectionName,
            filter: 'id in [1, 2, 3]',
            outputFields: ['title'],
        );
        $search = $milvus->vector()->search(
            collectionName: $collectionName,
            data: [createTestVector(0.1)],
            annsField: 'vector',
            limit: 1,
            outputFields: ['title'],
            consistencyLevel: 'Strong',
        );
        $filteredSearch = $milvus->vector()->search(
            collectionName: $collectionName,
            data: [createTestVector(0.2)],
            annsField: 'vector',
            filter: 'project_id == 10',
            limit: 3,
            outputFields: ['project_id'],
            consistencyLevel: 'Strong',
        );
        $filteredRows = $filteredSearch->collect('data');
        $filteredIds = $filteredRows
            ->pluck('id')
            ->map(static fn (int|string $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        expect($get->collect('data'))->toHaveCount(2)
            ->and($query->collect('data'))->toHaveCount(3)
            ->and($search->json('data.0.title'))->toBe('first')
            ->and($search->json('data.0.distance'))->toEqual(0)
            ->and($filteredSearch->json('code'))->toBe(0)
            ->and($filteredRows)->toHaveCount(2)
            ->and($filteredRows->pluck('project_id')->all())->each->toBe(10)
            ->and($filteredIds)->toBe(['1', '3']);

        if (str_starts_with((string) getenv('MILVUS_VERSION'), '3.')) {
            $searchById = $milvus->vector()->search(
                collectionName: $collectionName,
                data: null,
                annsField: 'vector',
                limit: 1,
                outputFields: ['title'],
                ids: [1],
            );

            expect($searchById->json('code'))->toBe(0)
                ->and($searchById->json('data.0.title'))->toBe('first');
        }

        $upsert = $milvus->vector()->upsert(
            collectionName: $collectionName,
            data: [
                ['id' => 2, 'vector' => createTestVector(0.2), 'title' => 'updated'],
            ],
        );
        $delete = $milvus->vector()->delete(
            collectionName: $collectionName,
            filter: 'id == 1',
        );

        expect($upsert->json('data.upsertCount'))->toBe(1)
            ->and($delete->json('data.deleteCount'))->toBe(1);

        sleep(1);

        expect($milvus->vector()->get(2, $collectionName, ['title'])->json('data.0.title'))->toBe('updated')
            ->and($milvus->vector()->get(1, $collectionName)->collect('data'))->toBeEmpty();

        if (str_starts_with((string) getenv('MILVUS_VERSION'), '3.')) {
            $partialUpdate = $milvus->vector()->upsert(
                collectionName: $collectionName,
                data: [['id' => 3, 'title' => 'partially updated']],
                partialUpdate: true,
            );

            expect($partialUpdate->json('data.upsertCount'))->toBe(1);

            sleep(1);

            expect($milvus->vector()->get(3, $collectionName, ['title'])->json('data.0.title'))
                ->toBe('partially updated');
        }
    } finally {
        if ($created) {
            $drop = $milvus->collections()->drop($collectionName);

            expect($drop->json('code'))->toBe(0);
        }
    }
});
