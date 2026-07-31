<?php

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Data\Response\EntityResponse;
use HelgeSverre\Milvus\Data\Response\MutationResponse;
use HelgeSverre\Milvus\Milvus;

it('round-trips a custom collection schema through live Milvus', function () {
    $milvus = $this->app->make(Milvus::class);
    $collectionName = 'php_schema_'.bin2hex(random_bytes(6));
    $created = false;

    try {
        $createDto = $milvus->collections()->create(
            collectionName: $collectionName,
            schema: [
                'autoID' => false,
                'enableDynamicField' => false,
                'fields' => [
                    [
                        'fieldName' => 'id',
                        'dataType' => 'Int64',
                        'isPrimary' => true,
                    ],
                    [
                        'fieldName' => 'embedding',
                        'dataType' => 'FloatVector',
                        'elementTypeParams' => ['dim' => '4'],
                    ],
                    [
                        'fieldName' => 'project_id',
                        'dataType' => 'Int64',
                    ],
                    [
                        'fieldName' => 'title',
                        'dataType' => 'VarChar',
                        'elementTypeParams' => ['max_length' => '128'],
                    ],
                ],
            ],
        )->dto()->throwIfFailed();
        $created = true;

        $describeDto = $milvus->collections()->describe($collectionName)->dto()->throwIfFailed();
        $fields = collect($describeDto->collection->fields)->keyBy('name');
        $fieldParameter = static function (array $params, string $key): mixed {
            if (! array_is_list($params)) {
                return $params[$key] ?? null;
            }

            return collect($params)->firstWhere('key', $key)['value'] ?? null;
        };

        expect($createDto)->toBeInstanceOf(EmptyResponse::class)
            ->and($describeDto)->toBeInstanceOf(CollectionDescriptionResponse::class)
            ->and($describeDto->collection->collectionName)->toBe($collectionName)
            ->and($describeDto->collection->autoId)->toBeFalse()
            ->and($describeDto->collection->enableDynamicField)->toBeFalse()
            ->and($fields->keys()->sort()->values()->all())
            ->toBe(['embedding', 'id', 'project_id', 'title'])
            ->and($fields['id']->type)->toBe('Int64')
            ->and($fields['embedding']->type)->toBe('FloatVector')
            ->and($fieldParameter($fields['embedding']->params, 'dim'))->toBe('4')
            ->and($fields['title']->type)->toBe('VarChar')
            ->and($fieldParameter($fields['title']->params, 'max_length'))->toBe('128');
    } finally {
        if ($created) {
            $milvus->collections()->drop($collectionName)->dto()->throwIfFailed();
        }
    }
});

it('inserts and retrieves auto-generated primary keys from live Milvus', function () {
    $milvus = $this->app->make(Milvus::class);
    $collectionName = 'php_auto_id_'.bin2hex(random_bytes(6));
    $created = false;

    try {
        $milvus->collections()->create(
            collectionName: $collectionName,
            dimension: 4,
            metricType: 'COSINE',
            autoID: true,
        )->dto()->throwIfFailed();
        $created = true;

        $insertDto = $milvus->vector()->insert(
            collectionName: $collectionName,
            data: [
                ['vector' => [1.0, 0.0, 0.0, 0.0], 'title' => 'first'],
                ['vector' => [0.0, 1.0, 0.0, 0.0], 'title' => 'second'],
            ],
        )->dto()->throwIfFailed();

        expect($insertDto)->toBeInstanceOf(MutationResponse::class)
            ->and($insertDto->result->insertCount)->toBe(2)
            ->and($insertDto->result->insertIds)->toHaveCount(2)
            ->and($insertDto->result->insertIds[0])->not->toBe($insertDto->result->insertIds[1]);

        $getDto = $milvus->vector()->get(
            id: $insertDto->result->insertIds,
            collectionName: $collectionName,
            outputFields: ['title'],
            consistencyLevel: 'Strong',
        )->dto()->throwIfFailed();
        $titles = collect($getDto->entities)
            ->map(static fn ($entity): mixed => $entity->field('title'))
            ->sort()
            ->values()
            ->all();

        expect($getDto)->toBeInstanceOf(EntityResponse::class)
            ->and($getDto->entities)->toHaveCount(2)
            ->and($titles)->toBe(['first', 'second']);
    } finally {
        if ($created) {
            $milvus->collections()->drop($collectionName)->dto()->throwIfFailed();
        }
    }
});
