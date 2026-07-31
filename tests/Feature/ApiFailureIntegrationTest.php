<?php

use HelgeSverre\Milvus\Data\Response\CollectionDescriptionResponse;
use HelgeSverre\Milvus\Data\Response\DatabaseDescriptionResponse;
use HelgeSverre\Milvus\Exceptions\MilvusApiException;
use HelgeSverre\Milvus\Milvus;

it('decodes a live missing-database error envelope', function () {
    $milvus = $this->app->make(Milvus::class);
    $databaseName = 'missing_'.bin2hex(random_bytes(6));
    $response = $milvus->databases()->describe($databaseName);
    $dto = $response->dto();

    expect($response->status())->toBe(200)
        ->and($response->json('code'))->not->toBe(0)
        ->and($dto)->toBeInstanceOf(DatabaseDescriptionResponse::class)
        ->and($dto->successful())->toBeFalse()
        ->and($dto->database)->toBeNull()
        ->and($dto->message)->toBeString()
        ->and(fn () => $dto->throwIfFailed())->toThrow(MilvusApiException::class);
});

it('decodes a live missing-collection error envelope', function () {
    $milvus = $this->app->make(Milvus::class);
    $collectionName = 'missing_'.bin2hex(random_bytes(6));
    $response = $milvus->collections()->describe($collectionName);
    $dto = $response->dto();

    expect($response->status())->toBe(200)
        ->and($response->json('code'))->not->toBe(0)
        ->and($dto)->toBeInstanceOf(CollectionDescriptionResponse::class)
        ->and($dto->successful())->toBeFalse()
        ->and($dto->collection)->toBeNull()
        ->and($dto->message)->toBeString()
        ->and(fn () => $dto->throwIfFailed())->toThrow(MilvusApiException::class);
});
