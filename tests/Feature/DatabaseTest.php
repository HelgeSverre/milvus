<?php

use HelgeSverre\Milvus\Data\Response\DatabaseDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseListResponse;
use HelgeSverre\Milvus\Data\Response\EmptyResponse;
use HelgeSverre\Milvus\Milvus;

it('lists and describes the default database', function () {
    $milvus = $this->app->make(Milvus::class);

    $list = $milvus->databases()->list();
    $listDto = $list->dto()->throwIfFailed();
    $describe = $milvus->databases()->describe('default');
    $describeDto = $describe->dto()->throwIfFailed();

    expect($list->status())->toBe(200)
        ->and($list->json('code'))->toBe(0)
        ->and($listDto)->toBeInstanceOf(DatabaseListResponse::class)
        ->and($listDto->databases)->toContain('default')
        ->and($describe->status())->toBe(200)
        ->and($describe->json('code'))->toBe(0)
        ->and($describeDto)->toBeInstanceOf(DatabaseDescriptionResponse::class)
        ->and($describeDto->database->dbName)->toBe('default')
        ->and(is_int($describeDto->database->dbId) || is_string($describeDto->database->dbId))->toBeTrue()
        ->and($describeDto->database->raw)->toHaveKeys(['dbName', 'dbID']);
});

it('supports the configured Milvus database lifecycle', function () {
    $milvus = $this->app->make(Milvus::class);
    $databaseName = 'php_client_'.bin2hex(random_bytes(6));
    $created = false;

    try {
        $create = $milvus->databases()->create(
            dbName: $databaseName,
            properties: ['database.replica.number' => 1],
        );
        $createDto = $create->dto()->throwIfFailed();
        $created = true;

        expect($create->status())->toBe(200)
            ->and($create->json('code'))->toBe(0)
            ->and($createDto)->toBeInstanceOf(EmptyResponse::class)
            ->and($createDto->data)->toBe([]);

        $listDto = $milvus->databases()->list()->dto()->throwIfFailed();
        $describeDto = $milvus->databases()->describe($databaseName)->dto()->throwIfFailed();
        $replicaProperty = collect($describeDto->database->properties)
            ->first(static fn ($property): bool => $property->key === 'database.replica.number');

        expect($listDto)->toBeInstanceOf(DatabaseListResponse::class)
            ->and($listDto->databases)->toContain($databaseName)
            ->and($describeDto)->toBeInstanceOf(DatabaseDescriptionResponse::class)
            ->and($describeDto->database->dbName)->toBe($databaseName)
            ->and($describeDto->database->dbId)->toBeInt()
            ->and($replicaProperty)->not->toBeNull()
            ->and($replicaProperty->value)->toBe('1');

        $dropDto = $milvus->databases()->drop($databaseName)->dto()->throwIfFailed();
        $created = false;

        expect($dropDto)->toBeInstanceOf(EmptyResponse::class)
            ->and($dropDto->data)->toBe([])
            ->and($milvus->databases()->list()->dto()->throwIfFailed()->databases)
            ->not->toContain($databaseName);
    } finally {
        if ($created) {
            $milvus->databases()->drop($databaseName)->dto()->throwIfFailed();
        }
    }
});
